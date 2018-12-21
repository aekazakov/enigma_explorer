@extends('template')

@section('title', 'Advanced Search')

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
        <a class="page-link" id="pageLinkFirst" href="javascript:void(0)" aria-label="previous">&laquo;</a>
      </li>
      <li class="page-item" id="prevPage">
        <a class="page-link" id="pageLinkPrev" href="javascript:void(0)" aria-label="previous">&lt;</a>
      </li>
      <li class="page-item" id="nextPage">
        <a class="page-link" id="pageLinkNext" href="javascript:void(0)" aria-label="previous">&gt;</a>
      </li>
      <li class="page-item" id="lastPage">
        <a class="page-link" id="pageLinkLast" href="javascript:void(0)" aria-label="next">&raquo;</a>
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
      // initially set page 1. Changed by FE
      var page = 1;

      // get post data
      var postData = JSON.parse(@json($postData));
      // get number of results
      $.post('/api/v1/isolates/multiKeywords', postData, function(data) {
        var resLen = data.length;
        $('#resNumber').html(resLen);
        if (resLen == 0) {
          emptyDataView();
          return;
        }
      });

      return [postData, page];
    };

    // Fetch list of isolates by API
    function fetchIsolates(postData, page) {
      $.ajax({
        url: '/api/v1/isolates/multiKeywords',
        type: 'POST',
        dataType: 'json',
        data: postData,
        success: function(data) {
          appendDataView(data, page, postData);
          genPagination(data, page, postData);
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
    function appendDataView(data, page, postData) {
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
                  <a href="/isolates/id/${data[entry].id}">${data[entry].isolate_id}</a>
                </strong></p>
              </div>
            </div>
            <div class="row">
              <div class="col-3">
                <p class="my-0"><i>ID:</i> ${data[entry].id}</p>
              </div>
              <div class="col-3">
                <p class="my-0"><i>Order:</i> ${data[entry].order}</p>
              </div>
              <div class="col-6">
                <p class="my-0"><i>Closest relative:</i> ${data[entry].closest_relative}</p>
              </div>
            </div>
          </div>
        `;
        $('#dataList').append(dataEntry);
      }
    };

    $(document).ready(function() {
      var [postData, page] = initialize();
      fetchIsolates(postData, page);
      $('#loadingIcon').remove();
    });
  </script>
@endsection
