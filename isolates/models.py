from django.db import models

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
    date_sampled = models.CharField(max_length=16)
    sample_id = models.CharField(max_length=32)
    lab = models.CharField(max_length=64)
    campaign = models.CharField(max_length=128)
    rrna = models.TextField()

    def __str__(self):
        return self.isolate_id
        
    @property
    def admin_name(self):
        return self.isolate_id + ' (' + self.order + ')'

