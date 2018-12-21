@extends('template')

@section('title')
Search: {{ $keyword }}
@endsection

@section('content')
  @component('searchBox')
    Search again?
  @endcomponent
  <div class="row my-2">
    <h3>Search results</h3>
  </div>
  <div class="row">
    <p><span id="resNumber"></span> <strong>Results</strong></p>
  </div>
  <div class="row my-2">
    <div class="col">
      <div class="sk-rotating-plane" id="loadingIcon"></div>
      <ul class="list-group-flush" id="dataList">
      </ul>
    </div>
  </div>
  <nav aria-label="pagination nav">
    <ul class="pagination justify-content-center" id="pagination">
      <li class="page-item" id="firstPage">
        <a class="page-link" href="javascript:void(0)" id="pageLinkFirst" aria-label="previous">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li class="page-item" id="prevPage">
        <a class="page-link" href="javascript:void(0)" id="pageLinkPrev" aria-label="previous">
          <span aria-hidden="true">&lt;</span>
        </a>
      </li>
      <li class="page-item" id="nextPage">
        <a class="page-link" href="javascript:void(0)" id="pageLinkNext" aria-label="previous">
          <span aria-hidden="true">&gt;</span>
        </a>
      </li>
      <li class="page-item" id="lastPage">
        <a class="page-link" href="javascript:void(0)" id="pageLinkLast" aria-label="next">
          <span aria-hidden="true">&raquo;</span>
        </a>
      </li>
    </ul>
  </nav>
  <script src="/js/search.js"></script>
  <script>

    // assign display numbers per page
    const PER_PAGE = 10;
    const MAX_PAGE = 7;
    
    // Set initial parameters (prob from template)
    function initialize() {
      // pass keyword by template
      var keyword = '{{ $keyword }}';

      // get number of results
      $.get('/api/v1/isolates/count/'+keyword, function(data) {
        resLen = data.count;
        $('#resNumber').html(resLen);
        if (resLen == 0) {
          emptyDataView();
          return;
        }
      });

      // initially set page 1. Changed by FE
      var page = 1;

      return [keyword, page];
    };

    // Fetch list of isolates by API
    function fetchIsolates(keyword, page) {
      // first get entire list from BE, display only a part
      // this would cause trouble if too many results
      $.ajax({
        url:'/api/v1/isolates/keyword/'+keyword,
        success: function(data) {
          appendDataView(data, page, keyword);
          genPagination(data, page, keyword);
        },
        error: function(jqXHR, text, code) {
          if (jqXHR.status == 400) {
            var errorHint = "Something went wrong :( Please check your keyword.";
          } else {
            var errorHint = "Unknown error. Please contact admin!";
          }
          errorDataView(errorHint);
        }
      });
    };

    // Append isolates (main data view) list
    function appendDataView(data, page, keyword) {
      // ajust order: if isolate exact matches, show first
      for (entry in data) {
        if (data[entry].isolate_id.toLowerCase() == keyword.toLowerCase()) {
          tmp = data[entry];
          data.splice(entry, 1);
          data.unshift(tmp);
          break;
        }
      }
      
      // iterate the range in current page
      var startEntry = (page-1)*PER_PAGE;
      var endEntry = Math.min(data.length,page*PER_PAGE);
      for (entry = startEntry; entry < endEntry; entry++) {
        // format html string for every data entry
        var dataEntry = `
          <div class="list-group-item">
            <div class="row">
              <div class="col">
                <p class="my-1"><strong>
                  <a href="/isolates/id/${data[entry].id}">${data[entry].isolate_id.replace(RegExp('('+keyword+')', 'i'), '<mark>$1</mark>')}</a>
                </strong></p>
              </div>
            </div>
            <div class="row">
              <div class="col-3">
                <p class="my-0"><i>ID:</i> ${data[entry].id}</p>
              </div>
              <div class="col-3">
                <p class="my-0"><i>Order:</i> ${data[entry].order.replace(RegExp('('+keyword+')', 'i'), '<mark>$1</mark>')}</p>
              </div>
              <div class="col-6">
                <p class="my-0"><i>Closest relative:</i> ${data[entry].closest_relative.replace(RegExp('('+keyword+')', 'i'), '<mark>$1</mark>')}</p>
              </div>
            </div>
          </div>`;
        $('#dataList').append(dataEntry);
      }
    };

    $(document).ready(function() {
      var [keyword, page] = initialize();
      fetchIsolates(keyword, page);
      $('#loadingIcon').remove();
    });
  </script>
@endsection
