import os
from django.core.management.base import BaseCommand
from isolates.util import download_isolates_gdrive

class Command(BaseCommand):
    help = 'Import new CORAL isolates from Google Drive spreadsheet.'

    def handle(self, *args, **options):
        download_isolates_gdrive()
