import MySQLdb
from isolates.models import *
# invoke by exec(open('check_atacama.py').read())
dateformat = '%Y-%m-%d'
datetimeformat = '%Y-%m-%d %H:%M:%S'

def clean_string_field(input):
    # There is a convention in Django to use empty strings instead of NULL values for string fields
    if input is None:
        return ''
    if input == 'NULL':
        return ''
    else:
        return str(input)

def connect_growth_db(host='', user='', password='', db=''):
    db=MySQLdb.connect(host=host, user=user, password=password, db=db)
    return db

def show_tables(db):
    query = "SHOW TABLES"
    c=db.cursor()
    c.execute(query)
    result = c.fetchall()
    return result
    
def get_growth_plate_ids(db):
    query = "SELECT GrowthPlate.GrowthPlateId FROM GrowthPlate"
    c=db.cursor()
    c.execute(query)
    result = []
    for item in c.fetchall():
        result.append(item[0])
    return result

def update_instruments(db):
    '''
        Checks the Instrument table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    existing_instrument_ids = set(Instrument.objects.values_list('instrumentId', flat=True))
    query = 'SELECT * FROM Instrument'
    print(query)
    c=db.cursor()
    c.execute(query)
    new_entries = []
    for item in c.fetchall():
        print(item)
        instrument_id = item[0]
        if instrument_id in existing_instrument_ids:
            continue
        if item[3] == '0000-00-00':
            start_date = datetime.date.min
        else:
            start_date = item[3]  #datetime.datetime.strptime(item[3], dateformat)
            #start_date = start_date.date()
        if item[4] is None:
            end_date = None
        else:
            if item[4] == 'NULL':
                end_date = None
            else:
                end_date = item[4]  #datetime.datetime.strptime(item[4], dateformat)
                #end_date = end_date.date()
        
        instrument = Instrument(
                        instrumentId = instrument_id,
                        instrumentName = clean_string_field(item[1]),
                        instrumentType = clean_string_field(item[2]),
                        startDate = start_date,
                        endDate = end_date,
                        comments = clean_string_field(item[5]),
                        instrumentModel = clean_string_field(item[6])
                        )
        new_entries.append(instrument)
    if new_entries:
        Instrument.objects.bulk_create(new_entries, batch_size=1000)
    print('New instruments created:', len(new_entries))
    
def update_plates(db):
    '''
        Checks the GrowthPlate table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    growth_plate_ids = get_growth_plate_ids(db)
    existing_growth_plate_ids = set(GrowthPlate.objects.values_list('growthPlateId', flat=True))
    plate_ids = [x for x in growth_plate_ids if x not in existing_growth_plate_ids]
    if not plate_ids:
        return
    print(len(plate_ids), 'new growth plates found')

    param = ','.join([str(x) for x in plate_ids])
    query = 'SELECT * FROM GrowthPlate WHERE GrowthPlate.growthPlateId IN ( ' + param + ' )'
    print(query)
    c=db.cursor()
    c.execute(query)

    instruments = {item.instrumentId:item for item in Instrument.objects.all()}
    new_entries = []
    for item in c.fetchall():
        #print(item)
        if item[3] == '0000-00-00 00:00:00':
            date_created = datetime.date.min
        else:
            date_created = item[3]  #datetime.datetime.strptime(item[3], datetimeformat)
        if item[4] == '0000-00-00 00:00:00':
            date_scanned = datetime.date.min
        else:
            date_scanned = item[4]  #datetime.datetime.strptime(item[4], datetimeformat)
        instrument = instruments[item[5]]
        plate = GrowthPlate(
                            growthPlateId = item[0],
                            plateType = clean_string_field(item[1]),
                            numberOfWells = clean_string_field(item[2]),
                            dateCreated = date_created,
                            dateScanned = date_scanned,
                            instrumentId = instrument,
                            headerInformation = clean_string_field(item[6]),
                            anaerobic = clean_string_field(item[7]),
                            measurement = clean_string_field(item[8]),
                            comment = clean_string_field(item[9])
                            )
        new_entries.append(plate)
    if new_entries:
        GrowthPlate.objects.bulk_create(new_entries, batch_size=1000)
    print('New plates created:', len(new_entries))

def update_strains(db):
    '''
        Checks the Strain table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    existing_strain_ids = set(Strain.objects.values_list('strainId', flat=True))
    query = 'SELECT * FROM Strain'
    print(query)
    c=db.cursor()
    c.execute(query)
    new_entries = []
    for item in c.fetchall():
        strain_id = item[0]
        if strain_id in existing_strain_ids:
            continue
        print(item)
        strain = Strain(
                        strainId = strain_id,
                        label = clean_string_field(item[1]),
                        taxonomyId = item[2],
                        taxPrefix = clean_string_field(item[3])
                        )
        new_entries.append(strain)
    if new_entries:
        Strain.objects.bulk_create(new_entries, batch_size=1000)
    print('New strains created:', len(new_entries))

def update_strain_mutants(db):
    '''
        Checks the StrainMutant table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT StrainMutant.strainMutantId FROM StrainMutant"
    c=db.cursor()
    c.execute(query)
    strain_mutant_ids = []
    for item in c.fetchall():
        strain_mutant_ids.append(item[0])

    existing_strain_mutant_ids = set(StrainMutant.objects.values_list('strainMutantId', flat=True))
    new_mutant_ids = [x for x in strain_mutant_ids if x not in existing_strain_mutant_ids]
    if not new_mutant_ids:
        print('No new strain mutants')
        return
    print(len(new_mutant_ids), 'new strain mutants found')

    param = ','.join([str(x) for x in new_mutant_ids])
    query = 'SELECT * FROM StrainMutant WHERE StrainMutant.strainMutantId IN ( ' + param + ' )'
    print(query)
    c=db.cursor()
    c.execute(query)

    strains = {item.strainId:item for item in Strain.objects.all()}
    new_entries = []
    for item in c.fetchall():
        #print(item)
        try:
            mutant = StrainMutant(
                                strainMutantId = item[0],
                                strainId = strains[item[1]],
                                wildType = clean_string_field(item[2]),
                                comments = clean_string_field(item[3]),
                                APA = item[4],
                                keep = item[5],
                                externalId = clean_string_field(item[6]),
                                nickname = clean_string_field(item[7])
                                )
            new_entries.append(mutant)
        except KeyError:
            continue
    if new_entries:
        StrainMutant.objects.bulk_create(new_entries, batch_size=1000)
    print('New mutants created:', len(new_entries))

def update_growth_wells(db):
    '''
        Checks the GrowthWell table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT GrowthWell.growthWellId FROM GrowthWell"
    print(query)
    c=db.cursor()
    c.execute(query)
    growth_well_ids = []
    for item in c.fetchall():
        growth_well_ids.append(item[0])

    existing_growth_well_ids = set(GrowthWell.objects.values_list('growthWellId', flat=True))
    new_well_ids = [x for x in growth_well_ids if x not in existing_growth_well_ids]
    if not new_well_ids:
        print('No new growth wells')
        return
    print(len(new_well_ids), 'new growth wells found')

    param = ','.join([str(x) for x in new_well_ids])
    query = 'SELECT * FROM GrowthWell WHERE GrowthWell.growthWellId IN ( ' + param + ' )'
    print(query)
    c=db.cursor()
    c.execute(query)

    strain_mutants = {item.strainMutantId:item for item in StrainMutant.objects.all()}
    plates = {item.growthPlateId:item for item in GrowthPlate.objects.all()}
    new_entries = []
    for item in c.fetchall():
        #print(item)
        try:
            well = GrowthWell(
                        growthWellId = item[0],
                        growthPlateId = plates[item[1]],
                        strainMutantId = strain_mutants[item[2]],
                        wellLocation = clean_string_field(item[3]),
                        wellRow = clean_string_field(item[4]),
                        wellCol = clean_string_field(item[5]),
                        media = clean_string_field(item[6]),
                        poolId = item[7],
                        subPool = clean_string_field(item[8]),
                        )
            new_entries.append(well)
        except KeyError:
            continue
    if new_entries:
        GrowthWell.objects.bulk_create(new_entries, batch_size=1000)
    print('New wells created:', len(new_entries))

def update_treatment_info(db):
    '''
        Checks the TreatmentInfo table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT TreatmentInfo.treatmentInfoId FROM TreatmentInfo"
    c=db.cursor()
    print(query)
    c.execute(query)
    treatment_ids = []
    for item in c.fetchall():
        treatment_ids.append(item[0])

    existing_treatment_ids = set(TreatmentInfo.objects.values_list('treatmentInfoId', flat=True))
    new_treatment_ids = [x for x in treatment_ids if x not in existing_treatment_ids]
    if not new_treatment_ids:
        print('No new treatment info')
        return
    print(len(new_treatment_ids), 'new treatment info found')

    param = ','.join([str(x) for x in new_treatment_ids])
    query = 'SELECT * FROM TreatmentInfo WHERE TreatmentInfo.treatmentInfoId IN ( ' + param + ' )'
    print(query)
    c=db.cursor()
    c.execute(query)

    growth_wells = {item.growthWellId:item for item in GrowthWell.objects.all()}
    new_entries = []
    for item in c.fetchall():
        #print(item)
        try:
            treatment = TreatmentInfo(
                        treatmentInfoId = item[0],
                        growthWellId = growth_wells[item[1]],
                        condition = clean_string_field(item[2]),
                        concentration = item[3],
                        units = clean_string_field(item[4])
                        )
            new_entries.append(treatment)
        except KeyError:
            continue
    if new_entries:
        TreatmentInfo.objects.bulk_create(new_entries, batch_size=1000)
    print('New TreatmentInfo created:', len(new_entries))

def import_well_data_batch(db, batch_ids, growth_wells):
    new_entries = []
    param = ','.join([str(x) for x in batch_ids])
    query = 'SELECT * FROM WellData WHERE WellData.wellDataId IN ( ' + param + ' )'
    #print(query)
    c=db.cursor()
    c.execute(query)
    new_entries = []
    for item in c.fetchall():
        #print(item)
        try:
            timepoint = int(item[2])
        except:
            timepoint = 0
        try:
            value = float(item[3])
        except:
            value = 0.0
        try:
            temperature = float(item[4])
        except:
            temperature = 0.0
        welldata = WellData(
                    wellDataId = item[0],
                    growthWellId = growth_wells[item[1]],
                    timepointSeconds = timepoint,
                    value = value,
                    temperature = temperature
                    )
        new_entries.append(welldata)
    if new_entries:
        WellData.objects.bulk_create(new_entries, batch_size=1000)
    print('New WellData created:', len(new_entries))

    
def update_well_data(db):
    '''
        Checks the WellData table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT WellData.wellDataId FROM WellData"
    c=db.cursor()
    print(query)
    c.execute(query)
    well_data_ids = []
    for item in c.fetchall():
        well_data_ids.append(item[0])

    existing_well_data_ids = set(WellData.objects.values_list('wellDataId', flat=True))
    new_well_data_ids = [x for x in well_data_ids if x not in existing_well_data_ids]
    if not new_well_data_ids:
        print('No new well data')
        return
    print(len(new_well_data_ids), 'new well data found')
    
    growth_wells = {item.growthWellId:item for item in GrowthWell.objects.all()}
    batch_size=10000
    batch_ids = []
    for well_data_id in new_well_data_ids:
        batch_ids.append(well_data_id)
        if len(batch_ids)%batch_size == 0:
            import_well_data_batch(db, batch_ids, growth_wells)
            batch_ids = []
    import_well_data_batch(db, batch_ids, growth_wells)
            

def update_plate_database(host='', user='', password='', db=''):
    db=connect_growth_db(host=host, user=user, password=password, db=db)
    print(show_tables(db))
    update_instruments(db)
    update_plates(db)
    update_strains(db)
    update_strain_mutants(db)
    update_growth_wells(db)
    update_treatment_info(db)
    update_well_data(db)

