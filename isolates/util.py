import os
import sys
import MySQLdb
import openpyxl
from pathlib import Path
from collections import defaultdict
from subprocess import Popen, PIPE, CalledProcessError, STDOUT
from django.core.mail import mail_admins
from isolates.models import *
from isolatebrowser.settings import BASE_DIR

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
    
def update_instruments(db):
    '''
        Checks the Instrument table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = 'SELECT Instrument.instrumentId FROM Instrument'
    #print(query)
    c=db.cursor()
    c.execute(query)
    instrument_ids = []
    for item in c.fetchall():
        instrument_ids.append(item[0])

    existing_instrument_ids = set(Instrument.objects.values_list('instrumentId', flat=True))
    new_instrument_ids = [x for x in instrument_ids if x not in existing_instrument_ids]
    if not new_instrument_ids:
        result = 'No new instruments'
        return result
    result = str(len(new_instrument_ids)) + ' new instruments found\n'
    param = ','.join([str(x) for x in new_instrument_ids])
    query = 'SELECT * FROM Instrument WHERE Instrument.instrumentId IN ( ' + param + ' )'
    #print(query)
    c=db.cursor()
    c.execute(query)
    new_entries = []
    for item in c.fetchall():
        #print(item)
        instrument_id = item[0]
        #if instrument_id in existing_instrument_ids:
        #    continue
        if item[3] == '0000-00-00' or item[3] is None:
            start_date = datetime.date.min
        else:
            start_date = item[3]
            #datetime.datetime.strptime(item[3], dateformat)
            #start_date = start_date.date()
        if item[4] is None:
            end_date = None
        else:
            if item[4] == 'NULL':
                end_date = None
            else:
                end_date = item[4]
                #datetime.datetime.strptime(item[4], dateformat)
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
        result += str(len(new_entries)) + ' new instruments created'
    else:
        result += 'No new instruments'
    return result

def update_plates(db):
    '''
        Checks the GrowthPlate table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    result = ''
    query = "SELECT GrowthPlate.GrowthPlateId FROM GrowthPlate"
    c=db.cursor()
    c.execute(query)
    growth_plate_ids = set()
    for item in c.fetchall():
        growth_plate_ids.add(item[0])
        
    existing_growth_plate_ids = GrowthPlate.objects.values_list('growthPlateId', flat=True)
    deleted_plate_ids = [x for x in existing_growth_plate_ids if x not in growth_plate_ids]
    if deleted_plate_ids:
        GrowthPlate.objects.filter(growthPlateId__in=deleted_plate_ids).delete()
        result += str(len(deleted_plate_ids)) + ' growth plates deleted'

    existing_growth_plate_ids = set(GrowthPlate.objects.values_list('growthPlateId', flat=True))
    new_plate_ids = [x for x in growth_plate_ids if x not in existing_growth_plate_ids]
    if not new_plate_ids:
        result += 'No new growth plates'
        return result
    result += str(len(new_plate_ids)) + ' new growth plates found\n'

    param = ','.join([str(x) for x in new_plate_ids])
    query = 'SELECT * FROM GrowthPlate WHERE GrowthPlate.growthPlateId IN ( ' + param + ' )'
    #print(query)
    c=db.cursor()
    c.execute(query)

    instruments = {item.instrumentId:item for item in Instrument.objects.all()}
    new_entries = []
    for item in c.fetchall():
        #print(item)
        if item[3] == '0000-00-00 00:00:00':
            date_created = datetime.date.min
        else:
            date_created = item[3]
            #datetime.datetime.strptime(item[3], datetimeformat)
        if item[4] == '0000-00-00 00:00:00' or item[4] is None:
            date_scanned = datetime.date.min
        else:
            date_scanned = item[4]
            #datetime.datetime.strptime(item[4], datetimeformat)
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
        result += str(len(new_entries)) + ' new growth plates created'
    else:
        result += 'No new growth plates'
    return result

def update_strains(db):
    '''
        Checks the Strain table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = 'SELECT Strain.strainId FROM Strain'
    #print(query)
    c=db.cursor()
    c.execute(query)
    strain_ids = []
    for item in c.fetchall():
        strain_ids.append(item[0])

    existing_strain_ids = set(Strain.objects.values_list('strainId', flat=True))
    new_strain_ids = [x for x in strain_ids if x not in existing_strain_ids]
    if not new_strain_ids:
        result = 'No new strains'
        return result
    result = str(len(new_strain_ids)) + ' new strains found\n'
    param = ','.join([str(x) for x in new_strain_ids])
    query = 'SELECT * FROM Strain WHERE Strain.strainId IN ( ' + param + ' )'

    #print(query)
    c=db.cursor()
    c.execute(query)
    new_entries = []
    for item in c.fetchall():
        strain_id = item[0]
        #print(item)
        strain = Strain(
                        strainId = strain_id,
                        label = clean_string_field(item[1]),
                        taxonomyId = item[2],
                        taxPrefix = clean_string_field(item[3])
                        )
        new_entries.append(strain)
    if new_entries:
        Strain.objects.bulk_create(new_entries, batch_size=1000)
        result += str(len(new_entries)) + ' new strains created'
    else:
        result += 'No new strains'
    return result

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
        result = 'No new strain mutants'
        return result
    result = str(len(new_mutant_ids)) + ' new strain mutants found\n'

    param = ','.join([str(x) for x in new_mutant_ids])
    query = 'SELECT * FROM StrainMutant WHERE StrainMutant.strainMutantId IN ( ' + param + ' )'
    #print(query)
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
        result += str(len(new_entries)) + ' new strain mutants created'
    else:
        result += 'No new strain mutants'
    return result

def update_growth_wells(db):
    '''
        Checks the GrowthWell table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT GrowthWell.growthWellId FROM GrowthWell"
    #print(query)
    c=db.cursor()
    c.execute(query)
    growth_well_ids = [item[0] for item in c.fetchall()]
    #for item in c.fetchall():
    #    growth_well_ids.append(item[0])

    existing_growth_well_ids = set(GrowthWell.objects.values_list('growthWellId', flat=True))
    new_well_ids = [x for x in growth_well_ids if x not in existing_growth_well_ids]
    if not new_well_ids:
        result = 'No new growth wells'
        return result
    result = str(len(new_well_ids)) + ' new growth wells found\n'

    param = ','.join([str(x) for x in new_well_ids])
    query = 'SELECT * FROM GrowthWell WHERE GrowthWell.growthWellId IN ( ' + param + ' )'
    #print(query)
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
        result += str(len(new_entries)) + ' new growth wells created'
    else:
        result += 'No new growth wells'
    return result

def update_treatment_info(db):
    '''
        Checks the TreatmentInfo table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT TreatmentInfo.treatmentInfoId FROM TreatmentInfo"
    c=db.cursor()
    #print(query)
    c.execute(query)
    treatment_ids = []
    for item in c.fetchall():
        treatment_ids.append(item[0])

    existing_treatment_ids = set(TreatmentInfo.objects.values_list('treatmentInfoId', flat=True))
    new_treatment_ids = [x for x in treatment_ids if x not in existing_treatment_ids]
    if not new_treatment_ids:
        result = 'No new treatment info'
        return result
    result = str(len(new_treatment_ids)) + ' new treatment info found\n'

    param = ','.join([str(x) for x in new_treatment_ids])
    query = 'SELECT * FROM TreatmentInfo WHERE TreatmentInfo.treatmentInfoId IN ( ' + param + ' )'
    #print(query)
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
        result += str(len(new_entries)) + ' new treatment info created'
    else:
        result += 'No new treatment info'
    return result

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
    return len(new_entries)
    
def update_well_data(db):
    '''
        Checks the WellData table at atacama for new entries and imports them into SQLite DB
        db: database handler
    '''
    query = "SELECT WellData.wellDataId FROM WellData"
    c=db.cursor()
    #print(query)
    c.execute(query)
    well_data_ids = []
    for item in c.fetchall():
        well_data_ids.append(item[0])

    existing_well_data_ids = set(WellData.objects.values_list('wellDataId', flat=True))
    new_well_data_ids = [x for x in well_data_ids if x not in existing_well_data_ids]
    if not new_well_data_ids:
        result = 'No new well data'
        return result
    result = str(len(new_well_data_ids)) + ' new well data found\n'
    
    growth_wells = {item.growthWellId:item for item in GrowthWell.objects.all()}
    batch_size=10000
    batch_ids = []
    new_entry_count = 0
    for well_data_id in new_well_data_ids:
        batch_ids.append(well_data_id)
        if len(batch_ids)%batch_size == 0:
            new_entry_count += import_well_data_batch(db, batch_ids, growth_wells)
            batch_ids = []
    new_entry_count += import_well_data_batch(db, batch_ids, growth_wells)
    if new_entry_count > 0:
        result += str(new_entry_count) + ' new well data created'
    else:
        result += 'No new well data'
    return result
            
def update_plate_database(host='', user='', password='', db=''):
    try:
        db=connect_growth_db(host=host, user=user, password=password, db=db)
    except Exception:
        mail_admins('ENIGMA Explorer growth data update: DB CONNECTION ERROR', f"Failed to connect to external database:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    result = []
    #print(show_tables(db))
    try:
        result.append(update_instruments(db))
    except Exception:
        mail_admins('ENIGMA Explorer instrument data update: ERROR', f"Failed to update instruments:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    try:
        result.append(update_plates(db))
    except Exception:
        mail_admins('ENIGMA Explorer plate data update: ERROR', f"Failed to update growth plates:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    try:
        result.append(update_strains(db))
    except Exception:
        mail_admins('ENIGMA Explorer strain data update: ERROR', f"Failed to update strains:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    try:
        result.append(update_strain_mutants(db))
    except Exception:
        mail_admins('ENIGMA Explorer mutant data update: ERROR', f"Failed to update strain mutants:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    try:
        result.append(update_growth_wells(db))
    except Exception:
        mail_admins('ENIGMA Explorer well data update: ERROR', f"Failed to update growth wells:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    try:
        result.append(update_treatment_info(db))
    except Exception:
        mail_admins('ENIGMA Explorer treatment data update: ERROR', f"Failed to update treatment info:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    try:
        result.append(update_well_data(db))
    except Exception:
        mail_admins('ENIGMA Explorer growth data update: ERROR', f"Failed to update well data:{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
        raise
    new_flag = False
    for log_line in result:
        if not log_line.startswith('No new'):
            new_flag = True
    if new_flag:
        subject = 'ENIGMA Explorer: growth data updated'
    else:
        subject = 'ENIGMA Explorer: no new growth data'
    message = '\n'.join(result)
    print(message)
    mail_admins(subject, message)
    return message

def download_isolates_gdrive():
    result= []
    try:
        remote_src = 'gdriveR:Shared_ENIGMA/DataManagement_ENIGMA/ENIGMA Data/ENIGMA Isolate List and Sequences/ENIGMA isolate data exported from CORAL.xlsx'
        local_dst = os.path.join(BASE_DIR,'pub','enigma_isolates.xlsx')
        if os.path.exists(local_dst):
            os.remove(local_dst)
        rcloneCmd = ['rclone', 'copyto', '--config', str(BASE_DIR) + '/rclone.conf', remote_src, '--drive-shared-with-me', local_dst]
        print(rcloneCmd)
        with Popen(rcloneCmd, stdin=PIPE, stdout=PIPE, stderr=STDOUT, bufsize=1, universal_newlines=True) as p:
            rcloneOut, err = p.communicate()
        print(rcloneOut)
        result.append(rcloneOut)
        if p.returncode != 0:
            print('rClone finished with error:' + str(err))
        if not os.path.exists(local_dst):
            print('rClone did not report any errors, but downloaded file is missing')
        xlsx_path = Path(local_dst)
        wb_obj = openpyxl.load_workbook(xlsx_path)
        sheet = wb_obj.active
        xlsx_header = []
        
        existing_strain_ids = set(Isolate.objects.values_list('isolate_id', flat=True))
        isolates_imported = defaultdict(dict)
        for i, row in enumerate(sheet.iter_rows(values_only=True)):
            if i == 0:
                xlsx_header = row[1:]
            else:
                strain_id = row[0]
                if strain_id == '' or strain_id is None:
                    continue
                if strain_id in existing_strain_ids:
                    #print(strain_id, 'already in the database')
                    continue
                result.append('New isolate found: ' + strain_id)
                for j, cell in enumerate(row[1:]):
                    if cell != '' and cell != 'None' and cell is not None:
                        isolates_imported[strain_id][xlsx_header[j]] = str(cell)
        #print(isolates_imported.keys())
        print(str(len(isolates_imported)), 'new isolates found')
        result.append(str(len(isolates_imported)) + ' new isolates found')
        '''
        fields = {'Isolation conditions/description (including temperature)':'condition',
                  'Phylogenetic Order':'order',
                  'Closest relative in NCBI: 16S rRNA Gene Database':'closest_relative',
                  'Similarity (%)':'similarity',
                  'Date sampled':'date_sampled',
                  'Well/Sample ID':'sample_id',
                  'Lab isolated/Contact':'lab',
                  'Campaign or Set':'campaign',
                  'rrna':'rrna'
                  }
        '''
        new_items = {}
        for isolate_id,isolate_data in isolates_imported.items():
            if isolate_id in new_items:
                result.append('Skipping ' + isolate_id + ' because it has been added already')
                continue
            if 'Taxon_ID_Order_Based_on_NCBI_16S_rRNA_BLAST' in isolate_data:
                order = isolate_data['Taxon_ID_Order_Based_on_NCBI_16S_rRNA_BLAST']
            else:
                order = ''
            if 'Description_Closest_relative_in_NCBI_16S_rRNA_Gene_Database' in isolate_data:
                closest_relative = isolate_data['Description_Closest_relative_in_NCBI_16S_rRNA_Gene_Database']
            else:
                closest_relative = ''
            if 'Sequence_Similarity_BLAST' in isolate_data:
                similarity = float(isolate_data['Sequence_Similarity_BLAST'])
            else:
                similarity = 0.0
            if 'Sequence_16S_Sequence' in isolate_data:
                rrna = isolate_data['Sequence_16S_Sequence']
            else:
                rrna = ''
            isolate = Isolate(
                              isolate_id = isolate_id,
                              condition = isolate_data['Isolation Condition, standardized (see column C for original description)'],
                              order = order,
                              closest_relative = closest_relative,
                              similarity = similarity,
                              #date_sampled = isolate_data['Sampling Date'],
                              #sample_id = isolate_data['Environmental_Sample_ID'],
                              #lab = isolate_data['ENIGMA_Labs_and_Personnel_Contact_Person_or_Lab'],
                              #campaign = isolate_data['ENIGMA_Campaign'],
                              rrna = rrna
                              )
            new_items[isolate_id] = isolate
        if new_items:
            Isolate.objects.bulk_create(new_items.values(), batch_size=1000)
            result.append(build_isolate_blastdb())
        print(str(len(new_items)), 'new isolates written')
        result.append(str(len(new_items)) + ' new isolates written')
        
        metadata_items = []
        for isolate_id,isolate_data in isolates_imported.items():
            isolate = Isolate.objects.get(isolate_id=isolate_id)
            for key,val in isolate_data.items():
                if key in [
                    'Sequence_16S_Sequence',
                    'Description_Closest_relative_in_NCBI_16S_rRNA_Gene_Database',
                    'Taxon_ID_Order_Based_on_NCBI_16S_rRNA_BLAST',
                    'Sequence_Similarity_BLAST'
                ]:
                    continue
                if ' ' in key:
                    display_name = ' '.join(key.split(' ')[:2])
                    display_name.strip(', ')
                    param = display_name.replace(' ', '_')
                else:
                    param = key
                    display_name = param.replace('_', ' ')
                if len(val) > 250:
                    val = val[:247] + '...'
                metadata_item = IsolateMetadata(
                    isolate=isolate,
                    param=param,
                    display_name=display_name,
                    value=val
                )
                metadata_items.append(metadata_item)
            
        if metadata_items:
            IsolateMetadata.objects.bulk_create(metadata_items, batch_size=1000)
        print(str(len(metadata_items)), 'new metadata items written')
        if new_items:
            subject = 'ENIGMA Explorer update: ' + str(len()) + ' new isolates'
        else:
            subject = 'ENIGMA Explorer update: no new isolates'
        
    except Exception:
        message = '\n'.join(result)
        mail_admins('ENIGMA Explorer isolates update: ERROR', f"Output:{message}\n{sys.exc_info()[0]}. {sys.exc_info()[1]}, {sys.exc_info()[2].tb_frame.f_code.co_filename}:{sys.exc_info()[2].tb_lineno}")
    message = '\n'.join(result)
    mail_admins(subject, message)
    return message

def build_isolate_blastdb():
    script_path = os.path.join(BASE_DIR, 'blastdb', 'make_isolate_blastdb.sh')
    with open(script_path, 'w') as outfile:
        outfile.write('cd ' + str(os.path.join(BASE_DIR, 'blastdb')) + '\n')
        outfile.write('ncbi-blast-2.9.0+/bin/makeblastdb -in enigma_isolates.fna -parse_seqids -blastdb_version 5 -title "enigma_isolates" -dbtype nucl -out enigma_isolates')
    with open(os.path.join(BASE_DIR, 'blastdb', 'enigma_isolates.fna'), 'w') as outfile:
        for item in Isolate.objects.values_list('isolate_id', 'rrna'):
            if item[1] is not None and item[1] != '' and item[1] != 'NULL':
                outfile.write('>' + item[0] + '\n' + item[1] + '\n')
    cmd = ['bash', script_path]
    with Popen(cmd, stdin=PIPE, stdout=PIPE, stderr=STDOUT, bufsize=1, universal_newlines=True) as p:
        out_text, err = p.communicate()
    print(out_text)
    return out_text
    