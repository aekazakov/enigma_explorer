import os
from django.core.management.base import BaseCommand
from isolatebrowser.settings import ATACAMA_HOST,ATACAMA_USER,ATACAMA_PASSWORD,ATACAMA_DB
from isolates.util import update_plate_database

class Command(BaseCommand):
    help = 'Import new growth plates from atacama.'

    def handle(self, *args, **options):
        update_plate_database(host=ATACAMA_HOST, user=ATACAMA_USER, password=ATACAMA_PASSWORD, db=ATACAMA_DB)
