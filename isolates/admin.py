from django.contrib import admin
from isolates.models import *

# Register your models here.
class IsolateAdmin(admin.ModelAdmin):
    list_display = ['isolate_id', 'order', 'closest_relative', 'sample_id']
    list_filter = ['order', 'campaign', 'lab']
    ordering = ['isolate_id']
    search_fields = ['isolate_id', 'order', 'closest_relative', 'sample_id']

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
    list_display = ['growthPlateId', 'numberOfWells', 'plateType', 'comment']
    list_filter = ['numberOfWells']
    ordering = ['growthPlateId']
    search_fields = ['growthPlateId', 'numberOfWells', 'instrumentId__id']
    autocomplete_fields = ['instrumentId']

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

