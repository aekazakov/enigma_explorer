from rest_framework import serializers
from .models import *

class IsolateSerializer(serializers.ModelSerializer):
    class Meta:
        model = Isolate
        fields = ["id", "isolate_id", "condition", "order", "closest_relative", "similarity", "date_sampled", "sample_id", "lab", "campaign", "rrna"]


class IsolateNoRnaSerializer(serializers.ModelSerializer):
    class Meta:
        model = Isolate
        fields = ["id", "isolate_id", "condition", "order", "closest_relative", "similarity", "date_sampled", "sample_id", "lab", "campaign"]        