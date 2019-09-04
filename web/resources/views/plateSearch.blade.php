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
    <p><span id="resNumber"></span> <strong>Result(s)</strong></p>
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
    
    // Fetch list of isolates by API
    function fetchPlates(keyword, page) {
      // first get entire list from BE, display only a part
      // this would cause trouble if too many results
      $.ajax({
        url:'/api/v1/growth/keyword/'+keyword,
        success: function(data) {
          console.log(data.length);
          if (data.length == 0) {
            emptyDataView();
          }
          appendDataView(data, page, keyword);
          genPagination(data, page, keyword);

          // remove loading icon
          $('#loadingIcon').remove();
        },
        error: function(jqXHR, text, code) {
          if (jqXHR.status == 400) {
            var errorHint = "No related plates found.";
          } else {
            var errorHint = "Unknown error. Please contact admin!";
          }
          errorDataView(errorHint);

          // remove loading icon
          $('#loadingIcon').remove();
        }
      });
    };

    // Append isolates (main data view) list
    function appendDataView(data, page, keyword) {
      // ajust order: if isolate exact matches, show first
      for (entry in data) {
        // Exact match will show on the front
        //console.log(data);
        //console.log(data[entry].strain, keyword)
        if (data[entry].strain.toLowerCase() == keyword.toLowerCase()) {
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
                  <a href="/growthcurve/id/${data[entry].growthPlateId}">Plate ${data[entry].growthPlateId}</a>
                </strong></p>
              </div>
            </div>
            <div class="row">
              <div class="col-3">
                <p class="my-0"><i>Number of Wells:</i> ${data[entry].numberOfWells}</p>
              </div>
              <div class="col-3">
                <p class="my-0"><i>Date Created:</i> ${data[entry].dateCreated}</p>
              </div>
              <div class="col-6">
                <p class="my-0"><i>Strain:</i> ${data[entry].strain.replace(RegExp('('+keyword+')', 'i'), '<mark>$1</mark>')}</p>
              </div>
            </div>
          </div>`;
        $('#dataList').append(dataEntry);
      }
    };

    $(document).ready(function() {
      let keyword = '{{ $keyword }}';
      fetchPlates(keyword, 1);

      // adjust the search box to search for plates
      var newRedirect = () => {
        let keyword = encodeURI($('#mainSearchInput').prop('value'));
        // if a number, jump directly
        if (!isNaN(parseInt(keyword))) {
          window.location.href = '/growthcurve/id/'+keyword;
        } else {
          window.location.href = '/growthsearch?keyword='+keyword;
        }
      };

      setInterval(() => {
        $('#mainSearchButton').unbind();
        $('#mainSearchInput').unbind();

        $('#mainSearchButton').click(newRedirect);
        $('#mainSearchInput').bind('keypress', function(event) {
          if (event.keyCode == '13') {
            newRedirect();
          }
        });
      }, 100);
    });
  </script>
@endsection
