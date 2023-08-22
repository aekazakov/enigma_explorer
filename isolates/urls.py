from django.urls import path
from django.conf import settings
from django.conf.urls.static import static

from . import views


urlpatterns = [
    path('', views.index, name='mainLink'),
    path('index/', views.index, name='mainLink'),
    path('isolates/', views.isolates, name='isolatesLink'),
    path('isolates/id/<int:id>', views.isolate_detail, name='isolateDetail'),
    path('growthcurve/', views.growth_curve, name='growthCurve'),
    path('growthcurve/id/<int:id>', views.growth_detail, name='growthDetail'),
    path('plateuploader/', views.plate_uploader, name='plateUploader'),
    path('growthsearch/', views.plate_search, name='plateSearch'),
    path('search/', views.search, name='search'),
    path('advsearch/', views.adv_search, name='advSearch'),
    path('browse/', views.browse, name='browse'),
    path('advsearchlist/', views.adv_search_list, name='advSearchList'),
    path('construction/<str:original>', views.construction, name='construction'),
    path('test/', views.test, name='test'),
    
    path('api/v1/ping', views.ApiPingView.as_view(), name='ping'),
    path('api/v1/isolates', views.IsolateListApiView.as_view(), name='IsolatesController'),
    path('api/v1/isolates/isoid/<str:isoid>', views.IsolateByIsoidApiView.as_view(), name='IsolatesSelectByIsoid'),
    path('api/v1/isolates/id/<int:id>', views.IsolateByIdApiView.as_view(), name='IsolatesSelectById'),
    path('api/v1/isolates/keyword/<str:keyword>', views.IsolateByKeywordApiView.as_view(), name='IsolatesSelectByKeyword'),
    path('api/v1/isolates/genus/<str:genus>', views.IsolateByGenusApiView.as_view(), name='IsolatesSelectByGenus'),
    path('api/v1/isolates/count/<str:keyword>', views.IsolateCountByKeywordApiView.as_view(), name='IsolatesCountByKeyword'),
    path('api/v1/isolates/hint/<str:keyword>', views.TaxaHintApiView.as_view(), name='IsolatesTaxaHint'),
    path('api/v1/isolates/rrna/<int:id>', views.RrnaByIdApiView.as_view(), name='IsolatesRrnaById'),
    path('api/v1/isolates/orders/', views.GetOrdersApiView.as_view(), name='IsolatesGetOrders'),
    path('api/v1/isolates/genera/', views.GetGeneraApiView.as_view(), name='IsolatesGetGenera'),
    path('api/v1/isolates/taxa/', views.GetTaxaApiView.as_view(), name='IsolatesGetTaxa'),
    path('api/v1/isolates/taxa/rrna', views.Download16SApiView.as_view(), name='IsolatesDownload16S'),
    path('api/v1/isolates/multiKeywords', views.IsolateByMultiKeywordsApiView.as_view(), name='IsolatesSelectByMultiKeywords'),
    path('api/v1/isolates/relativeGenome/<int:id>', views.RelativeGenomeApiView.as_view(), name='IsolatesGenomeList'),

    path('api/v1/ncbi/genome/<int:id>', views.GetGenomeByNcbiIdApiView.as_view(), name='IsolatesGenomeByNcbiId'),
    path('api/v1/ncbi/blast/rid/<int:id>', views.BlastRidByIdApiView.as_view(), name='BlastRidById'),
    path('api/v1/ncbi/blast/<str:blastdb>', views.BlastBySeqApiView.as_view(), name='BlastBySeqId'),
    path('api/v1/ncbi/blast/<str:blastdb>/<int:id>', views.BlastByIdApiView.as_view(), name='BlastById'),

    path('api/v1/growth/meta/id/<int:id>', views.GrowthMetaByIdApiView.as_view(), name='GrowthMetaById'),
    path('api/v1/growth/wells/id/<int:id>', views.GrowthWellDataByIdApiView.as_view(), name='GrowthWellDataById'),
    path('api/v1/growth/keyword/<str:keyword>', views.GrowthMetaByKeywordApiView.as_view(), name='GrowthMetaByKeyword'),
    
    ] + static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)

handler404 = views.handler404
