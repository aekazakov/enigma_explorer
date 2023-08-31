import json
import re
import os, io, zipfile
from collections import Counter
from django.shortcuts import render
from django.template import loader
from django.http import HttpResponse, JsonResponse
from django.http import Http404
#from django.views import View
from django.db.models import Q
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from rest_framework import permissions
from rest_framework.exceptions import PermissionDenied
from rest_framework.decorators import api_view, renderer_classes
from rest_framework.renderers import JSONRenderer

from .models import *
from .serializers import IsolateSerializer, IsolateNoRnaSerializer
from .fetchGenome import fetchG, fetchS
from .localBlast import local_blast
from .remoteBlast import qblast
from isolatebrowser.settings import BLASTN_PATH, ENIGMA_DB, NCBI_DB, SILVA_DB

class APIErrorException(PermissionDenied):
    status_code = status.HTTP_400_BAD_REQUEST
    default_detail = "Custom Exception Message"
    default_code = 'invalid'

    def __init__(self, detail, status_code=None):
        self.detail = detail
        if status_code is not None:
            self.status_code = status_code

# API views

class IsolateListApiView(APIView):
    # get all isolates
    def get(self, request, *args, **kwargs):
        '''
        List all isolates in the database
        '''
        isolates = Isolate.objects.all()
        serializer = IsolateSerializer(isolates, many=True)
        return Response(serializer.data, status=status.HTTP_200_OK)

        
class ApiPingView(APIView):
    # alive test
    def get(self, request):
        return Response({'message':'pong'})

        
class IsolateByIsoidApiView(APIView):
    # select isolates by isolate id
    def get_object(self, isolate_id):
        '''
        Helper method to get the object with given id
        '''
        try:
            return Isolate.objects.get(isolate_id=isolate_id)
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, isoid, *args, **kwargs):
        '''
        Returns the isolate for given id
        '''
        isolate = self.get_object(isoid)
        if isolate is None:
            raise APIErrorException(detail={'message': 'Isolate not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        serializer = IsolateSerializer(isolate, many=False)
        return Response(serializer.data, status=status.HTTP_200_OK)

        
class IsolateByIdApiView(APIView):
    # select isolates by id
    def get_object(self, id):
        '''
        Helper method to get the object with given id
        '''
        try:
            return Isolate.objects.get(id=id)
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, id, *args, **kwargs):
        '''
        Returns the isolate for given id
        '''
        isolate = self.get_object(id=id)
        if isolate is None:
            raise APIErrorException(detail={'message': 'Isolate not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        serializer = IsolateSerializer(isolate, many=False)
        return Response(serializer.data, status=status.HTTP_200_OK)

        
class IsolateByKeywordApiView(APIView):
    # select isolates by keyword
    def get_objects(self, keyword):
        '''
        Helper method to get the objects matching keyword
        '''
        try:
            return Isolate.objects.filter(Q(isolate_id__icontains=keyword) | Q(order__icontains=keyword) | Q(closest_relative__icontains=keyword)).order_by('isolate_id')
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, *args, **kwargs):
        '''
        Returns list of isolates matching for given keyword
        '''
        keyword = kwargs['keyword']
        if len(keyword) < 3:
            raise APIErrorException(detail={'message': 'Too short query keyword'}, status_code=status.HTTP_400_BAD_REQUEST)
        isolates = self.get_objects(keyword)
        if isolates is None:
            raise APIErrorException(detail={'message': 'Isolate not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        serializer = IsolateSerializer(isolates, many=True)
        return Response(serializer.data, status=status.HTTP_200_OK)

        
class IsolateByGenusApiView(APIView):
    # select a list of isolates by genus
    def get_objects(self, genus):
        '''
        Helper method to get the objects which closest relative starts with the given genus
        '''
        try:
            return Isolate.objects.filter(Q(closest_relative__startswith=genus)).order_by('isolate_id')
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, *args, **kwargs):
        '''
        Returns list of isolates which closest relative starts with the given genus
        '''
        genus = kwargs['genus']
        isolates = self.get_objects(genus)
        if isolates is None:
            raise APIErrorException(detail={'message': 'Isolate not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        serializer = IsolateSerializer(isolates, many=True)
        return Response(serializer.data, status=status.HTTP_200_OK)

        
class IsolateCountByKeywordApiView(APIView):
    # get match number by keyword
    def get_objects(self, keyword):
        '''
        Helper method to get the objects matching keyword
        '''
        try:
            return Isolate.objects.filter(Q(isolate_id__icontains=keyword) | Q(order__icontains=keyword) | Q(closest_relative__icontains=keyword)).order_by('isolate_id')
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, *args, **kwargs):
        '''
        Returns list of isolates matching for given keyword
        '''
        keyword = kwargs['keyword']
        if len(keyword) < 3:
            raise APIErrorException(detail={'message': 'Too short query keyword'}, status_code=status.HTTP_400_BAD_REQUEST)
        isolates = self.get_objects(keyword)
        if isolates is None:
            raise APIErrorException(detail={'message': 'Isolate not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        return Response({'count':isolates.count()}, status=status.HTTP_200_OK)

        
class TaxaHintApiView(APIView):
    # get hints by keyword
    def get(self, request, *args, **kwargs):
        '''
        Returns list of isolates which closest relative starts with the given genus
        '''
        MAX_HINT_LEN = 5
        result = []
        keyword = kwargs['keyword']
        if len(keyword) < 3:
            raise APIErrorException(detail={'message': 'Too short query keyword'}, status_code=status.HTTP_400_BAD_REQUEST)
        for item in Isolate.objects.filter(Q(closest_relative__icontains=keyword)).order_by('id').values_list('closest_relative', flat=True).distinct():
            for word in item.split():
                if re.search(keyword, word, re.IGNORECASE) is not None:
                    if word not in result:
                        result.append(word)
        for item in Isolate.objects.filter(Q(order__icontains=keyword)).order_by('id').values_list('order', flat=True).distinct():
            if item not in result:
                result.append(item)
        if len(result) > MAX_HINT_LEN:
            result = result[:MAX_HINT_LEN]
        return Response(result, status=status.HTTP_200_OK)

        
class RrnaByIdApiView(APIView):
    # get 16s rrna seq by id
    def get_object(self, id):
        '''
        Helper method to get the object with given id
        '''
        try:
            return Isolate.objects.get(id=id)
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, id, *args, **kwargs):
        '''
        Returns the isolate for given id
        '''
        isolate = self.get_object(id=id)
        if isolate is None:
            raise APIErrorException(detail={'message': 'Isolate not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        if isolate.rrna is None:
            raise APIErrorException(detail={'message': 'No 16S seq record found of the isolate'}, status_code=status.HTTP_404_NOT_FOUND)
            
        response = HttpResponse(content_type='text/plain')
        response['Content-Disposition'] = 'attachment; filename="' + isolate.isolate_id  + '.fa"'
        response.write('>' + isolate.isolate_id + '\n' + isolate.rrna)
        return response

        
class GetOrdersApiView(APIView):
    # retrieve a full list of orders
    def get_objects(self):
        '''
        Helper method to get the list of orders
        '''
        try:
            return Isolate.objects.values_list('order', flat=True)
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, *args, **kwargs):
        '''
        Returns list of orders
        '''
        order_stats = Counter()
        orders = self.get_objects()
        for order in orders:
            order_stats[order] += 1
        if orders is None:
            raise APIErrorException(detail={'message': 'Isolates not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        result = {}
        for order in sorted(order_stats.keys()):
            result[order] = order_stats[order]
        return Response(result, status=status.HTTP_200_OK)

        
class GetGeneraApiView(APIView):
    # retrieve a list of genera
    def get_objects(self):
        '''
        Helper method to get the list of closest relatives
        '''
        try:
            return Isolate.objects.values_list('closest_relative', flat=True)
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, *args, **kwargs):
        '''
        Returns list of genera
        '''
        genera_stats = Counter()
        relatives = self.get_objects()
        for organism in relatives:
            if organism == '':
                genera_stats[organism] += 1
            else:
                genera_stats[organism.split()[0]] += 1
        if relatives is None:
            raise APIErrorException(detail={'message': 'Isolates not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        result = {}
        for genus in sorted(genera_stats.keys()):
            result[genus] = genera_stats[genus]
        return Response(result, status=status.HTTP_200_OK)

        
class GetTaxaApiView(APIView):
    # get hierarchical taxonomy
    def get_orders(self):
        '''
        Helper method to get the list of orders
        '''
        try:
            return Isolate.objects.values_list('order', flat=True)
        except Isolate.DoesNotExist:
            return None

    def get_genera(self, order):
        '''
        Helper method to get the list of closest relatives
        '''
        try:
            return Isolate.objects.filter(order=order).values_list('closest_relative', flat=True)
        except Isolate.DoesNotExist:
            return None
            
    def get(self, request, *args, **kwargs):
        '''
        Returns list of isolates matching for given keyword
        '''
        result = {}
        order_counts = Counter()
        orders = self.get_orders()
        if orders is None:
            raise APIErrorException(detail={'message': 'Isolates not found'}, status_code=status.HTTP_400_BAD_REQUEST)
        for order in orders:
            order_counts[order] += 1
        for order in sorted(order_counts.keys()):
            result[order] = {}
            relatives = self.get_genera(order)
            genera_stats = Counter()
            for organism in relatives:
                if organism == '':
                    genera_stats[organism] += 1
                else:
                    genera_stats[organism.split()[0]] += 1
            genera_report = {}
            species_count = 0
            for genus in sorted(genera_stats.keys()):
                genera_report[genus] = genera_stats[genus]
                species_count += genera_stats[genus]
            result[order]['genera'] = genera_report
            result[order]['tSpecies'] = species_count
            result[order]['nGenera'] = len(genera_report)
        return Response(result, status=status.HTTP_200_OK)
        
class Download16SApiView(APIView):
    # download multiple 16s
    def post(self, request, format=None):
        '''
        Returns archived directory tree with 16S files
        '''
        #print(request.data)
        isolate_ids = []
        for key in request.data.keys():
            if key == 'csrfmiddlewaretoken':
                continue
            isolate_ids += request.data.getlist(key)
        print(isolate_ids)

        isolates = Isolate.objects.filter(id__in=isolate_ids).values_list('isolate_id', 'order', 'closest_relative', 'rrna')
        print(isolates.count())
        
        if isolates.count() == 0:
            raise APIErrorException(detail={'message': 'No isolates match your query'}, status_code=status.HTTP_404_NOT_FOUND)

        response_content = ''
        for item in isolates:
            response_content += '>' + '|'.join(item[:3]) + '\n' + item[3] + '\n'

        buffer = io.StringIO(response_content)
        response = HttpResponse(buffer.getvalue())
        response['Content-Type'] = 'text/plain'
        response['Content-Disposition'] = 'attachment; filename="download16S.fa"'
        return response

class IsolateByMultiKeywordsApiView(APIView):
    # select isolates by multiple keywords
    def post(self, request, *args, **kwargs):
        '''
        Returns list of isolates matching given keywords
        '''
        FIELDS = ('isoid', 'order', 'relative', 'wellnum', 'lab')
        print(request.data)
        search_options = {}
        search_params = {}
        for key in request.data.keys():
            if 'csrfmiddlewaretoken' in key:
                continue
            val = request.data[key]
            #key = key.split('keywords')[-1]
            if 'isEqual' in key:
                option = key[:-2].split('[')[-1]
                search_options[option] = val
            else:
                search_params[key[:-1].split('[')[-1]] = val
            print('\"' + key + '\"', val)
        print(search_options, search_params)
        
        qObjects = []
        for field in FIELDS:
            if field in search_params:
                if field in search_options:
                    if field == 'isoid':
                        if search_options[field] == 'true':
                            qObjects.append(Q(isolate_id=search_params[field]))
                        else:
                            qObjects.append(Q(isolate_id__icontains=search_params[field]))
                    elif field == 'order':
                        if search_options[field] == 'true':
                            qObjects.append(Q(order=search_params[field]))
                        else:
                            qObjects.append(Q(order__icontains=search_params[field]))
                    elif field == 'relative':
                        if search_options[field] == 'true':
                            qObjects.append(Q(closest_relative=search_params[field]))
                        else:
                            qObjects.append(Q(closest_relative__icontains=search_params[field]))
                    elif field == 'wellnum':
                        if search_options[field] == 'true':
                            qObjects.append(Q(sample_id=search_params[field]))
                        else:
                            qObjects.append(Q(sample_id__icontains=search_params[field]))
                    elif field == 'lab':
                        if search_options[field] == 'true':
                            qObjects.append(Q(lab=search_params[field]))
                        else:
                            qObjects.append(Q(lab__icontains=search_params[field]))
            
        isolates = Isolate.objects.filter(*qObjects).order_by('isolate_id')
        print(isolates.count(), 'isolates found')
        serializer = IsolateNoRnaSerializer(isolates, many=True)
        return Response(serializer.data, status=status.HTTP_200_OK)

class RelativeGenomeApiView(APIView):
    # get a list of relative genomes by id
    def get(self, request, id, *args, **kwargs):
        '''
        Returns list of genomes matching species name of the closest relative
        '''
        isolate = Isolate.objects.get(id=id)
        species = ' '.join(isolate.closest_relative.split(' ')[:2])
        strain = ' '.join(isolate.closest_relative.split(' ')[2:])
        genome_ids, genome_species = fetchG(species, strain)
        #mock-up
        #genome_ids = ["2068264705","2272060572","2524578363","2046638082","1608668069","1606814885","2015735303","1442440266","1817079518","423092765"]
        #genome_species = ["Pseudomonas fluorescens strain MYb115","Pseudomonas fluorescens strain ZL22","Pseudomonas fluorescens strain PH.SM","Pseudomonas fluorescens strain G20-18","Pseudomonas fluorescens strain LBUM677","Pseudomonas fluorescens strain 2P24","Pseudomonas fluorescens strain YK-310","Pseudomonas fluorescens strain NEP1","Pseudomonas fluorescens strain DR397","Pseudomonas fluorescens Q2-87"]
        response_content = {'id':genome_ids, 'strain':genome_species}
        return Response(response_content, status=status.HTTP_200_OK)

class GetGenomeByNcbiIdApiView(APIView):
    # get a genome fasta file by NCBI id
    def get(self, request, id, *args, **kwargs):
        '''
        Returns FASTA file of a NCBI genome by genome id
        '''
        id = str(id)
        genome_seq = fetchS(id)
        if genome_seq == '':
            raise APIErrorException(detail={'message': 'Sequence corresponds to the NCBI id is not found'}, status_code=status.HTTP_404_NOT_FOUND)
        response = HttpResponse(content_type='text/plain')
        response['Content-Disposition'] = 'attachment; filename="' + id  + '.fa"'
        response.write(genome_seq)
        return response

class BlastBySeqApiView(APIView):
    # blast against local db, using seq instead of id of isolates
    def post(self, request, blastdb, *args, **kwargs):
        '''
        Runs local BLASTN for any query sequence and returns the hits
        '''
        if blastdb == 'ncbi':
            db_path = NCBI_DB
        elif blastdb == 'silva':
            db_path = SILVA_DB
        elif blastdb == 'isolates':
            db_path = ENIGMA_DB
        else:
            raise APIErrorException(detail={'message': 'BLAST database not supported'}, status_code=status.HTTP_400_BAD_REQUEST)
        #print(self.request.data)
        #print('Path', BLASTN_PATH)
        #print('DB', db_path)
        #print('Query', self.request.data['info[seq]'])
        res = local_blast(BLASTN_PATH, db_path, self.request.data['info[seq]'])
        #print(res)
        if 'message' in res:
            raise APIErrorException(detail=res, status_code=status.HTTP_400_BAD_REQUEST)
        return Response(res, status=status.HTTP_200_OK)

class BlastByIdApiView(APIView):
    # perform local blast against isolates 16s
    def get(self, request, blastdb, id, *args, **kwargs):
        '''
        Runs local BLASTN for a 16S sequence from isolate database and returns the hits
        '''
        if blastdb == 'ncbi':
            db_path = NCBI_DB
        elif blastdb == 'silva':
            db_path = SILVA_DB
        elif blastdb == 'isolates':
            db_path = ENIGMA_DB
        else:
            raise APIErrorException(detail={'message': 'BLAST database not supported'}, status_code=status.HTTP_400_BAD_REQUEST)
        isolate = Isolate.objects.get(id=id)
        res = local_blast(BLASTN_PATH, db_path, '>' + isolate.isolate_id + '\n' + isolate.rrna + '\n')
        if 'message' in res:
            raise APIErrorException(detail=res, status_code=status.HTTP_400_BAD_REQUEST)
        return Response(res, status=status.HTTP_200_OK)

class BlastRidByIdApiView(APIView):
    # get a ncbi blast RID along with other form data
    def get(self, request, id, *args, **kwargs):
        '''
        Runs NCBI BLASTN for a 16S sequence from isolate database and returns the hits
        '''
        isolate = Isolate.objects.get(id=id)
        try:
            rid = qblast('blastn', 'nr', '>' + isolate.isolate_id + '\n' + isolate.rrna + '\n',format_type='Text',megablast=True)
        except ValueError as e:
            raise APIErrorException(detail={'message': str(e)}, status_code=status.HTTP_400_BAD_REQUEST)
        res = {'CMD':'Get', 'FORMAT_TYPE':'HTML', 'RID':rid, 'SHOW_OVERVIEW':'on'}
        return Response(res, status=status.HTTP_200_OK)

class GrowthMetaByIdApiView(APIView):
    # get plate meta by id
    def get(self, request, id, *args, **kwargs):
        '''
        get metadata by id
        '''
        plate = GrowthPlate.objects.get(growthPlateId=id)
        result = {'growthPlateId': plate.growthPlateId,
                  'plateType': plate.plateType,
                  'numberOfWells': plate.numberOfWells,
                  'dateCreated': plate.dateCreated.strftime("%Y-%m-%d %H:%M:%S"),
                  'dateScanned': plate.dateScanned.strftime("%Y-%m-%d %H:%M:%S"),
                  'instrumentName': plate.instrumentId.instrumentName,
                  'anaerobic': plate.anaerobic,
                  'measurement': plate.measurement}
        return Response(result, status=status.HTTP_200_OK)

class GrowthWellDataByIdApiView(APIView):
    # get actuall plate value by id
    def get(self, request, id, *args, **kwargs):
        '''
           in summary, we got for each well
           growthWellId, wellLocation (wellRow, wellCol), media, strain label, treatment (condition, concentration & unit)
           we got for each timepoint
           timepoint(in seconds), value, temperature
        '''
        wells = GrowthWell.objects.filter(growthPlateId__growthPlateId=id).select_related('strainMutantId__strainId', 'growthPlateId')
        result = []
        for well in wells:
            res = {'wellLocation': well.wellLocation,
                   'wellRow':well.wellRow,
                   'wellCol':well.wellCol,
                   'media':well.media,
                   'strainLabel':well.strainMutantId.strainId.label,
                   'treatment':{},
                   'data':{}
                   }
            treatment = TreatmentInfo.objects.get(growthWellId=well)
            res['treatment']['condition'] = treatment.condition
            res['treatment']['concentration'] = treatment.concentration
            res['treatment']['units'] = treatment.units
            res['data']['timepoints'] = []
            res['data']['values'] = []
            res['data']['temperatures'] = []
            data_points = WellData.objects.filter(growthWellId=well).values_list('timepointSeconds', 'value', 'temperature').order_by('timepointSeconds')
            for item in data_points:
                res['data']['timepoints'].append(item[0])
                res['data']['values'].append(item[1])
                res['data']['temperatures'].append(item[2])
            result.append(res)
        return Response(result, status=status.HTTP_200_OK)

class GrowthMetaByKeywordApiView(APIView):
    # get a list of plates by keyword (strain)
    def get(self, request, keyword, *args, **kwargs):
        '''
        Returns growth plate list filtered by a given keyword
        '''
        wells = GrowthWell.objects.filter(strainMutantId__strainId__label__icontains=keyword).select_related('strainMutantId__strainId', 'growthPlateId').values_list('growthPlateId__growthPlateId', 'growthPlateId__numberOfWells', 'growthPlateId__dateCreated', 'strainMutantId__strainId__label').distinct()
        result = []
        '''
        for well in wells:
            res = {'growthPlateId': well.growthPlateId.growthPlateId,
                   'numberOfWells': well.growthPlateId.numberOfWells,
                   'dateCreated': well.growthPlateId.dateCreated,
                   'strain': well.strainMutantId.strainId.label
                   }
        '''
        for item in wells:
            res = {'growthPlateId': item[0],
                   'numberOfWells': item[1],
                   'dateCreated': item[2].strftime("%Y-%m-%d %H:%M:%S"),
                   'strain': item[3]
                   }
            result.append(res)
        return Response(result, status=status.HTTP_200_OK)

        
# Front-end views.

def handler404(request, exception=None):
    '''
        Returns 404 page
    '''
    return render(request, '404.html', status=404)

def index(request):
    '''
        Displays main page
    '''
    template = loader.get_template('isolates/index.html')
    context = {}
    return HttpResponse(template.render(context, request))

def isolate_detail(request, id):
    '''
    path('isolates/id/<int:id>', views.isolate_detail, name='isolateDetail'),
    '''
    template = loader.get_template('isolates/detail.html')
    context = {'id': id}
    return HttpResponse(template.render(context, request))

def isolates(request):
    '''
    path('isolates/', views.isolates, name='isolatesLink'),
    '''
    template = loader.get_template('isolates/isolates.html')
    context = {'slot': ''}
    return HttpResponse(template.render(context, request))

def growth_detail(request, id):
    '''
    path('growthcurve/id/<int:id>', views.growth_detail, name='growthDetail'),
    '''
    template = loader.get_template('isolates/growthdetail.html')
    context = {'id': id}
    return HttpResponse(template.render(context, request))

def growth_curve(request):
    '''   
    path('growthcurve/', views.growth_curve, name='growthcurve'),
    '''
    template = loader.get_template('isolates/growthcurve.html')
    context = {'slot': ''}
    return HttpResponse(template.render(context, request))

def plate_uploader(request):
    '''
    path('plateuploader/', views.plate_uploader, name='plateUploader'),
    '''
    template = loader.get_template('isolates/plateuploader.html')
    context = {}
    return HttpResponse(template.render(context, request))

def plate_search(request):
    '''
    path('growthsearch/', views.plate_search, name='plateSearch'),
    '''
    template = loader.get_template('isolates/growthsearch.html')
    keyword = request.GET.get('keyword')
    context = {'keyword':keyword}
    return HttpResponse(template.render(context, request))

def search(request):
    '''
    path('search/', views.search, name='search'),
    '''
    template = loader.get_template('isolates/search.html')
    keyword = request.GET.get('keyword')
    context = {'keyword':keyword}
    return HttpResponse(template.render(context, request))

def adv_search(request):
    '''
    path('advsearch/', views.adv_search, name='advSearch'),
    '''
    template = loader.get_template('isolates/advsearch.html')
    context = {}
    return HttpResponse(template.render(context, request))

def browse(request):
    '''
    path('browse/', views.browse, name='browse'),
    '''
    template = loader.get_template('isolates/browse.html')
    context = {}
    return HttpResponse(template.render(context, request))
    
def adv_search_list(request):
    '''
    path('advsearchlist/', views.adv_search_list, name='advSearchList'),
    '''
    template = loader.get_template('isolates/advsearchlist.html')
    context = {}
    if request.method == "POST":
        post_data = {}
        for key, val in request.POST.items():
            if key == 'isEqual[csrfmiddlewaretoken]':
                continue
            if key == 'csrfmiddlewaretoken':
                continue
            post_data[key] = val
        context = {'postData': json.dumps(post_data)}
    print(context)
    return HttpResponse(template.render(context, request))

def construction(request, original):
    '''
    path('construction/<str:original>', views.construction, name='construction'),
    '''
    template = loader.get_template('isolates/construction.html')
    context = {'original': original}
    return HttpResponse(template.render(context, request))
    
def test(request):
    '''
    path('test/', views.test, name='test'),
    '''
    template = loader.get_template('isolates/test.html')
    context = {}
    return HttpResponse(template.render(context, request))
