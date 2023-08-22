from django.contrib import admin
from isolates.models import *

# Register your models here.
admin.site.register(Isolate)
admin.site.register(Instrument)
admin.site.register(GrowthPlate)
admin.site.register(Strain)
admin.site.register(StrainMutant)
admin.site.register(GrowthWell)
admin.site.register(TreatmentInfo)
admin.site.register(WellData)