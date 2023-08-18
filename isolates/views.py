from django.shortcuts import render
from django.template import loader
from django.http import HttpResponse

# Create your views here.

def handler404(request, exception):
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
    pass

def isolates(request):
    '''
    path('isolates/', views.isolates, name='isolatesLink'),
    '''
    pass

def growth_detail(request, id):
    '''
    path('growthcurve/id/<int:id>', views.growth_detail, name='growthDetail'),
    '''
    pass

def growth_curve(request):
    '''   
    path('growthcurve/', views.growth_curve, name='growthCurve'),
    '''
    pass

def plate_uploader(request):
    '''
    path('plateuploader/', views.plate_uploader, name='plateUploader'),
    '''
    pass

def plate_search(request):
    '''
    path('growthsearch/', views.plate_search, name='plateSearch'),
    '''
    pass

def search(request):
    '''
    path('search/', views.search, name='search'),
    '''
    pass

def adv_search(request):
    '''
    path('advsearch/', views.adv_search, name='advSearch'),
    '''
    pass

def browse(request):
    '''
    path('browse/', views.browse, name='browse'),
    '''
    pass
    
def adv_search_list(request):
    '''
    path('advsearchlist/', views.adv_search_list, name='advSearchList'),
    '''
    pass

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
    pass
