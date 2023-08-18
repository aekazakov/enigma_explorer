from django.urls import path
from django.conf import settings
from django.conf.urls.static import static

from . import views

urlpatterns = [
    path('', views.index, name='mainLink'),
    path('index/', views.index, name='mainLink'),
    path('isolates/id/<int:id>', views.isolate_detail, name='isolateDetail'),
    path('isolates/', views.isolates, name='isolatesLink'),
    path('growthcurve/id/<int:id>', views.growth_detail, name='growthDetail'),
    path('growthcurve/', views.growth_curve, name='growthCurve'),
    path('plateuploader/', views.plate_uploader, name='plateUploader'),
    path('growthsearch/', views.plate_search, name='plateSearch'),
    path('search/', views.search, name='search'),
    path('advsearch/', views.adv_search, name='advSearch'),
    path('browse/', views.browse, name='browse'),
    path('advsearchlist/', views.adv_search_list, name='advSearchList'),
    path('construction/<str:original>', views.construction, name='construction'),
    path('test/', views.test, name='test'),

] + static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)

handler404 = views.handler404
