from rest_framework import serializers
from .models import *

class IsolateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Isolate
        fields = ["id"]

class IsolateNoRnaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Isolate
        fields = ["id", "isolate_id", "condition", "order", "closest_relative", "similarity"]


class InstrumentSerializer(serializers.ModelSerializer):
    class Meta:
        model = Instrument
        fields = ['instrumentName']

