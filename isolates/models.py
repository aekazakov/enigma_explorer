import datetime
from django.db import models
from django.utils.translation import gettext_lazy as _

# Create your models here.

class Isolate(models.Model):
    '''
        Stores isolate data
    '''
    isolate_id = models.CharField(max_length=32, unique=True, db_index=True)
    condition = models.TextField()
    order = models.CharField(max_length=64)
    closest_relative = models.TextField()
    similarity = models.FloatField()
    #date_sampled = models.CharField(max_length=16)
    #sample_id = models.CharField(max_length=32)
    #lab = models.CharField(max_length=64)
    #campaign = models.CharField(max_length=128)
    rrna = models.TextField()

    def __str__(self):
        return self.isolate_id
        
    @property
    def admin_name(self):
        return self.isolate_id + ' (' + self.order + ')'

class IsolateMetadata(models.Model):
    '''
        Stores isolate data
    '''
    isolate = models.ForeignKey(Isolate, on_delete=models.CASCADE)
    param = models.CharField(max_length=128)
    display_name = models.CharField(max_length=128)
    value = models.CharField(max_length=250)

    def __str__(self):
        return self.param + ': ' + self.value
        
class Instrument(models.Model):
    '''
        Stores instrument data
    '''
    class InstrumentType(models.TextChoices):
        OD_PLATE_READER = "OD", _("od plate reader")
    
    instrumentId = models.IntegerField(unique=True, db_index=True)
    instrumentName = models.CharField(max_length=255, db_index=True)
    instrumentType = models.CharField(max_length=2, choices=InstrumentType.choices, default=InstrumentType.OD_PLATE_READER)
    startDate = models.DateField(default=datetime.date.min)
    endDate = models.DateField(blank=True, null=True)
    comments = models.CharField(max_length=255, blank=True)
    instrumentModel = models.CharField(max_length=255)

    def __str__(self):
        return self.instrumentName
        
    @property
    def admin_name(self):
        return self.instrumentName + ' (' + self.instrumentId + ')'
        

class GrowthPlate(models.Model):
    '''
        Stores growth plate data
    '''
    class PlateType(models.TextChoices):
        PRESCREEN = "PS", _("Pre-screen")
        SINGLEMUTANT = "SM", _("Single Mutant")

        
    class NumberOfWells(models.TextChoices):
        _24 = "24", _("24")
        _48 = "48", _("48")
        _96 = "96", _("96")
        _200 = "200", _("200")
        _384 = "384", _("384")
        

    class Anaerobic(models.TextChoices):
        YES = "YE", _("Yes")
        NO = "NO", _("No")


    growthPlateId = models.IntegerField(unique=True, db_index=True)
    plateType = models.CharField(max_length=2, choices=PlateType.choices, default=PlateType.PRESCREEN)
    numberOfWells = models.CharField(max_length=3, choices=NumberOfWells.choices, default=NumberOfWells._96)
    dateCreated = models.DateTimeField(auto_now_add=True)
    dateScanned = models.DateTimeField(default=datetime.datetime.min)
    instrumentId = models.ForeignKey('Instrument', on_delete=models.SET_NULL, blank=True, null=True)
    headerInformation = models.TextField(blank=True)
    anaerobic = models.CharField(max_length=2, choices=Anaerobic.choices, default=Anaerobic.NO)
    measurement = models.TextField(blank=True)
    comment = models.TextField()

    def __str__(self):
        return str(self.growthPlateId)
        
    @property
    def admin_name(self):
        return str(self.growthPlateId) + ' (' + self.numberOfWells + ' wells, ' + self.plateType + ')'
        

class Strain(models.Model):
    '''
        Stores strain data
    '''
    strainId = models.IntegerField(unique=True, db_index=True)
    label = models.CharField(max_length=255, blank=True, unique=True, db_index=True)
    taxonomyId = models.IntegerField(default=0)
    taxPrefix = models.CharField(max_length=255, unique=True, db_index=True)

    def __str__(self):
        return self.label
        
    @property
    def admin_name(self):
        return self.label + ' (' + str(self.taxonomyId) + ')'


class StrainMutant(models.Model):
    '''
        Stores strain mutant data
    '''
    class WildType(models.TextChoices):
        YES = "YE", _("Yes")
        NO = "NO", _("No")

    strainMutantId = models.IntegerField(unique=True, db_index=True)
    strainId = models.ForeignKey('Strain', on_delete=models.CASCADE, blank=True)
    wildType = models.CharField(max_length=2, choices=WildType.choices, default=WildType.YES)
    comments = models.CharField(max_length=255, blank=True)
    APA = models.IntegerField(blank=True, null=True, default=None, db_index=True)
    keep = models.SmallIntegerField(default=0, db_index=True)
    externalId = models.CharField(max_length=30, blank=True, default='', db_index=True)
    nickname = models.CharField(max_length=30, blank=True, default='', db_index=True)

    def __str__(self):
        if self.nickname == '':
            return str(self.strainMutantId) + ' (' + self.strainId.label + ')'
        else:
            return str(self.strainMutantId) + ' (' + self.strainId.label + ', ' + self.nickname + ')'
        
    @property
    def admin_name(self):
        if self.nickname == '':
            return str(self.strainMutantId) + ' (' + self.strainId.label + ')'
        else:
            return str(self.strainMutantId) + ' (' + self.strainId.label + ', ' + self.nickname +  ')'

    class Meta:
        indexes = [
            models.Index(fields=["APA"], name="apa_key_idx"),
            models.Index(fields=["keep"], name="smu_keep_idx"),
        ]        


class GrowthWell(models.Model):
    '''
        Stores growth well data
    '''
    class WellRow(models.TextChoices):
        A = "A", _("A")
        B = "B", _("B")
        C = "C", _("C")
        D = "D", _("D")
        E = "E", _("E")
        F = "F", _("F")
        G = "G", _("G")
        H = "H", _("H")
        I = "I", _("I")
        J = "J", _("J")
        K = "K", _("K")
        L = "L", _("L")
        M = "M", _("M")
        N = "N", _("N")
        O = "O", _("O")
        P = "P", _("P")

        
    class WellCol(models.TextChoices):
        _01 = "01", _("01")
        _02 = "02", _("02")
        _03 = "03", _("03")
        _04 = "04", _("04")
        _05 = "05", _("05")
        _06 = "06", _("06")
        _07 = "07", _("07")
        _08 = "08", _("08")
        _09 = "09", _("09")
        _10 = "10", _("10")
        _11 = "11", _("11")
        _12 = "12", _("12")
        _13 = "13", _("13")
        _14 = "14", _("14")
        _15 = "15", _("15")
        _16 = "16", _("16")
        _17 = "17", _("17")
        _18 = "18", _("18")
        _19 = "19", _("19")
        _20 = "20", _("20")
        _21 = "21", _("21")
        _22 = "22", _("22")
        _23 = "23", _("23")
        _24 = "24", _("24")
        

    class SubPool(models.TextChoices):
        UP = "UP", _("Up")
        DN = "DN", _("Down")


    growthWellId = models.IntegerField(unique=True, db_index=True)
    growthPlateId = models.ForeignKey('GrowthPlate', on_delete=models.CASCADE, blank=True, null=True)
    strainMutantId = models.ForeignKey('StrainMutant', on_delete=models.CASCADE, blank=True, null=True)
    wellLocation = models.CharField(max_length=3)
    wellRow = models.CharField(max_length=1, choices=WellRow.choices, blank=True, default='')
    wellCol = models.CharField(max_length=2, choices=WellCol.choices, blank=True, default='')
    media = models.CharField(max_length=255, db_index=True)
    poolId = models.IntegerField(blank=True, null=True, default=0, db_index=True)
    subPool = models.CharField(max_length=2, choices=SubPool.choices, blank=True, default='')

    def __str__(self):
        if self.wellLocation == '':
            return 'plate ' + str(self.growthPlateId.growthPlateId) + '; well ' + self.wellRow + self.wellCol
        else:
            return 'plate ' + str(self.growthPlateId.growthPlateId) + '; well ' + self.wellLocation
        
    @property
    def admin_name(self):
        if self.wellLocation == '':
            return 'plate ' + str(self.growthPlateId.growthPlateId) + '; well ' + self.wellRow + self.wellCol
        else:
            return 'plate ' + str(self.growthPlateId.growthPlateId) + '; well ' + self.wellLocation
        
    class Meta:
        indexes = [
            models.Index(fields=["media"], name="GrowthWell_ibfk_3"),
        ]        

class TreatmentInfo(models.Model):
    '''
        Stores treatment information
    '''
    treatmentInfoId = models.IntegerField(unique=True, db_index=True)
    growthWellId = models.ForeignKey('GrowthWell', on_delete=models.CASCADE, blank=True, null=True)
    condition = models.CharField(max_length=255, db_index=True)
    concentration = models.FloatField(default=0.0)
    units = models.CharField(max_length=255, db_index=True)

    def __str__(self):
        return str(self.treatmentInfoId)
        
    @property
    def admin_name(self):
        return str(self.treatmentInfoId) + ' (' + self.condition + ' ' + str(self.concentration) + self.units + ')'


class WellData(models.Model):
    '''
        Stores well data point
    '''
    wellDataId = models.IntegerField(unique=True, db_index=True)
    growthWellId = models.ForeignKey('GrowthWell', on_delete=models.CASCADE, blank=True, null=True)
    timepointSeconds = models.IntegerField(default = 0)
    value = models.FloatField(default=0.0)
    temperature = models.FloatField(default=0.0)

    def __str__(self):
        return str(self.wellDataId)
        
    @property
    def admin_name(self):
        return str(self.wellDataId)
