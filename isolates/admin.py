from django.contrib import admin
from django.shortcuts import render
from django.urls import path
from isolates.models import *
from isolates.util import download_isolates_gdrive, update_plate_database
from isolatebrowser.settings import ATACAMA_HOST,ATACAMA_USER,ATACAMA_PASSWORD,ATACAMA_DB

admin.site.site_header = "ENIGMA Explorer admin"
admin.site.site_title = "ENIGMA Explorer Admin Portal"
admin.site.index_title = "Welcome to ENIGMA Explorer administration portal"

# Register your models here.
class IsolateAdmin(admin.ModelAdmin):
    list_display = ['isolate_id', 'order', 'closest_relative', 'condition']
    list_filter = ['order']
    ordering = ['isolate_id']
    search_fields = ['isolate_id', 'order', 'closest_relative', 'condition']

    def get_urls(self):
        urls = super().get_urls()
        my_urls = [
            path('update-isolates/', self.update_isolates),
        ]
        return my_urls + urls

    def update_isolates(self, request):
        context = {}
        message = download_isolates_gdrive()
        context['result'] = message
        return render(
            request, "admin/update_isolates.html", context
        )
    
admin.site.register(Isolate, IsolateAdmin)


class InstrumentAdmin(admin.ModelAdmin):
    list_display = ['instrumentId', 'instrumentName', 'instrumentType']
    list_filter = ['instrumentType']
    ordering = ['instrumentId']
    search_fields = ['instrumentId', 'instrumentName', 'instrumentType']

admin.site.register(Instrument, InstrumentAdmin)


class StrainAdmin(admin.ModelAdmin):
    list_display = ['strainId', 'label', 'taxonomyId', 'taxPrefix']
    ordering = ['strainId']
    search_fields = ['strainId', 'label']
admin.site.register(Strain, StrainAdmin)


class GrowthPlateAdmin(admin.ModelAdmin):
    list_display = ['growthPlateId', 'numberOfWells', 'get_type', 'comment', 'get_instrument']
    list_filter = ['numberOfWells']
    ordering = ['growthPlateId']
    search_fields = ['growthPlateId', 'numberOfWells', 'instrumentId__id']
    autocomplete_fields = ['instrumentId']
    
    def get_instrument(self, obj):
        return obj.instrumentId.instrumentName
    get_instrument.admin_order_field  = 'instrumentId'  #Allows column order sorting
    get_instrument.short_description = 'Instrument'  #Renames column head

    def get_type(self, obj):
        return obj.get_plateType_display()
    get_type.short_description = 'Plate type'  #Renames column head
    
    def get_urls(self):
        urls = super().get_urls()
        my_urls = [
            path('update-plates/', self.update_plates),
        ]
        return my_urls + urls

    def update_plates(self, request):
        context = {}
        message = update_plate_database(host=ATACAMA_HOST, user=ATACAMA_USER, password=ATACAMA_PASSWORD, db=ATACAMA_DB)
        context['result'] = message
        return render(
            request, "admin/update_plates.html", context
        )

admin.site.register(GrowthPlate, GrowthPlateAdmin)


class StrainMutantAdmin(admin.ModelAdmin):
    list_display = ['strainMutantId', 'strainId', 'externalId', 'nickname', 'APA']
    list_filter = ['wildType', 'keep']
    ordering = ['strainMutantId']
    search_fields = ['strainMutantId', 'externalId', 'nickname']
    autocomplete_fields = ['strainId']
admin.site.register(StrainMutant, StrainMutantAdmin)


class GrowthWellAdmin(admin.ModelAdmin):
    list_display = ['growthWellId', 'wellLocation', 'wellRow', 'wellCol', 'growthPlateId', 'strainMutantId', 'media']
    ordering = ['growthWellId']
    search_fields = ['growthWellId']
    autocomplete_fields = ['growthPlateId', 'strainMutantId']
admin.site.register(GrowthWell, GrowthWellAdmin)

class TreatmentInfoAdmin(admin.ModelAdmin):
    list_display = ['treatmentInfoId', 'growthWellId', 'condition', 'concentration', 'units']
    ordering = ['treatmentInfoId']
    search_fields = ['treatmentInfoId', 'condition']
    autocomplete_fields = ['growthWellId']
admin.site.register(TreatmentInfo, TreatmentInfoAdmin)

class WellDataAdmin(admin.ModelAdmin):
    list_display = ['wellDataId', 'growthWellId', 'timepointSeconds', 'value', 'temperature']
    ordering = ['wellDataId']
    search_fields = ['wellDataId']
    autocomplete_fields = ['growthWellId']
admin.site.register(WellData, WellDataAdmin)

class IsolateMetadataAdmin(admin.ModelAdmin):
    list_display = ['isolate', 'param', 'display_name', 'value']
    list_filter = ['param']
    ordering = ['isolate']
    search_fields = ['param', 'display_name', 'value']

admin.site.register(IsolateMetadata, IsolateMetadataAdmin)
