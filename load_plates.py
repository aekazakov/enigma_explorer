import csv
import datetime
from isolates.models import *
instrument_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/Instrument.csv'
plate_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/GrowthPlate.csv'
strain_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/Strain.csv'
strainmutant_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/StrainMutant.csv'
well_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/GrowthWell.csv'
treatment_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/TreatmentInfo.csv'
welldata_file = '/mnt/data/work/ENIGMAExplorer/enigma_explorer/mysql_data/WellData.csv'

dateformat = '%Y-%m-%d'
datetimeformat = '%Y-%m-%d %H:%M:%S'

def clean_string_field(input):
    # There is a convention in Django to use empty strings instead of NULL values for string fields
    if input == 'NULL':
        return ''
    else:
        return str(input)

# Load instruments
instruments = []
with open(instrument_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        instrument_id = int(row[0])
        if row[3] == '0000-00-00':
            start_date = datetime.date.min
        else:
            start_date = datetime.datetime.strptime(row[3], dateformat)
            start_date = start_date.date()
        if row[4] == 'NULL':
            end_date = None
        else:
            end_date = datetime.datetime.strptime(row[3], dateformat)
            end_date = end_date.date()
        item = Instrument(
                        instrumentId = instrument_id,
                        instrumentName = clean_string_field(row[1]),
                        instrumentType = clean_string_field(row[2]),
                        startDate = start_date,
                        endDate = end_date,
                        comments = clean_string_field(row[5]),
                        instrumentModel = clean_string_field(row[6])
                        )
        instruments.append(item)
Instrument.objects.bulk_create(instruments, batch_size=1000)
print('Loaded instrument data')

instruments = {}
for item in Instrument.objects.all():
    instruments[item.instrumentId] = item

# Load growth plates
plates = []
with open(plate_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        growth_plate_id = int(row[0])
        if row[3] == '0000-00-00 00:00:00':
            date_created = datetime.date.min
        else:
            date_created = datetime.datetime.strptime(row[3], datetimeformat)
        if row[4] == '0000-00-00 00:00:00':
            date_scanned = datetime.date.min
        else:
            date_scanned = datetime.datetime.strptime(row[4], datetimeformat)
        item = GrowthPlate(
                        growthPlateId = growth_plate_id,
                        plateType = clean_string_field(row[1]),
                        numberOfWells = clean_string_field(row[2]),
                        dateCreated = date_created,
                        dateScanned = date_scanned,
                        instrumentId = instruments[int(row[5])],
                        headerInformation = clean_string_field(row[6]),
                        anaerobic = clean_string_field(row[7]),
                        measurement = clean_string_field(row[8]),
                        comment = clean_string_field(row[9])
                        )
        plates.append(item)
GrowthPlate.objects.bulk_create(plates, batch_size=1000)
print('Loaded plates data')

plates = {}
for item in GrowthPlate.objects.all():
    plates[item.growthPlateId] = item
        
# Load strains
strains = []
with open(strain_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        strain_id = int(row[0])
        item = Strain(
                    strainId = strain_id,
                    label = clean_string_field(row[1]),
                    taxonomyId = int(row[2]),
                    taxPrefix = clean_string_field(row[3])
                    )
        strains.append(item)
Strain.objects.bulk_create(strains, batch_size=1000)
print('Loaded strains data')

strains = {}
for item in Strain.objects.all():
    strains[item.strainId] = item
        
# Load strain mutants
strain_mutants = []
with open(strainmutant_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        strain_mutant_id = int(row[0])
        if row[4] == 'NULL':
            apa = None
        else:
            apa = int(row[4])
        if row[5] == 'NULL':
            keep = None
        else:
            keep = int(row[5])
        item = StrainMutant(
                    strainMutantId = strain_mutant_id,
                    strainId = strains[int(row[1])],
                    wildType = clean_string_field(row[2]),
                    comments = clean_string_field(row[3]),
                    APA = apa,
                    keep = keep,
                    externalId = clean_string_field(row[6]),
                    nickname = clean_string_field(row[7])
                    )
        strain_mutants.append(item)
StrainMutant.objects.bulk_create(strain_mutants, batch_size=1000)
print('Loaded strain mutants data')

strain_mutants = {}
for item in StrainMutant.objects.all():
    strain_mutants[item.strainMutantId] = item
        
# Load growth wells
growth_wells = []
with open(well_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        growth_well_id = int(row[0])
        if row[7] == 'NULL':
            pool_id = None
        else:
            pool_id = int(row[7])
        item = GrowthWell(
                    growthWellId = growth_well_id,
                    growthPlateId = plates[int(row[1])],
                    strainMutantId = strain_mutants[int(row[2])],
                    wellLocation = clean_string_field(row[3]),
                    wellRow = clean_string_field(row[4]),
                    wellCol = clean_string_field(row[5]),
                    media = clean_string_field(row[6]),
                    poolId = pool_id,
                    subPool = clean_string_field(row[8]),
                    )
        growth_wells.append(item)
GrowthWell.objects.bulk_create(growth_wells, batch_size=1000)
print('Loaded growth well data')

growth_wells = {}
for item in GrowthWell.objects.all():
    growth_wells[item.growthWellId] = item
        
# Load treatment info
treatment = []
with open(treatment_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        try:
            concentration = float(row[3])
        except:
            concentration = 0.0
        item = TreatmentInfo(
                    treatmentInfoId = int(row[0]),
                    growthWellId = growth_wells[int(row[1])],
                    condition = clean_string_field(row[2]),
                    concentration = concentration,
                    units = clean_string_field(row[4])
                    )
        treatment.append(item)
TreatmentInfo.objects.bulk_create(treatment, batch_size=1000)
print('Loaded treatment info')

treatment = {}
for item in TreatmentInfo.objects.all():
    treatment[item.treatmentInfoId] = item
        
# Load well data
well_data = []
with open(welldata_file, newline='') as csvfile:
    csvreader = csv.reader(csvfile)
    header = next(csvreader)
    for row in csvreader:
        growth_well_id = int(row[0])
        try:
            timepoint = int(row[2])
        except:
            timepoint = 0
        try:
            value = float(row[3])
        except:
            value = 0.0
        try:
            temperature = float(row[4])
        except:
            temperature = 0.0
        item = WellData(
                    wellDataId = int(row[0]),
                    growthWellId = growth_wells[int(row[1])],
                    timepointSeconds = timepoint,
                    value = value,
                    temperature = temperature
                    )
        well_data.append(item)
WellData.objects.bulk_create(well_data, batch_size=1000)
print('Loaded well data.')
print('That\'s all, folks!')
