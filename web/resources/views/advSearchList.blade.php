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
        <a class="page-link" href="#" aria-label="previous">&laquo;</a>
      </li>
      <li class="page-item" id="prevPage">
        <a class="page-link" href="#" aria-label="previous">&lt;</a>
      </li>
      <li class="page-item" id="nextPage">
        <a class="page-link" href="#" aria-label="previous">&gt;</a>
      </li>
      <li class="page-item" id="lastPage">
        <a class="page-link" href="#" aria-label="next">&raquo;</a>
      </li>
    </ul>
  </nav>

  <script src="/js/jquery.redirect.js"></script>
  <script>
    $(document).ready(function() {
      // get number of results
      var postData = JSON.parse(@json($postData));
      $.post('/api/v1/isolates/multiKeywords', postData, function(data) {
        $('#resNumber').html(data.length);
      });

      // assign display numbers per page
      const PER_PAGE = 10;
      // get page number from template
      // note this must be a integer. If not, it will be filtered by router
      var page = parseInt('{{ $page }}');

      // get list of isolates
      $.ajax({
        url: '/api/v1/isolates/multiKeywords',
        type: 'POST',
        dataType: 'json',
        data: postData,
        success: function(data) {
          // remove loading icon
          $('#loadingIcon').remove();

          //handle no results
          if (data.length == 0) {
            $('#dataList').append(`
              <div class="list-group-item">
                <p><strong>
                  Nothing found. Try another keyword?
                </p></strong>
              </div>
            `);
            return;
          }

          // first get entire list from BE, display only a part
          // TODO: his would cause trouble if too many results

          for (entry=(page-1)*PER_PAGE; entry<Math.min(data.length,page*PER_PAGE); entry++) {
            $('#dataList').append(`
              <div class="list-group-item">
                <div class="row">
                  <div class="col">
                    <p><strong>
                      <a href="/isolates/id/${data[entry].id}">${data[entry].isolate_id}</a>
                    </strong></p>
                  </div>
                </div>
                <div class="row">
                  <div class="col-3">
                    <p><i>ID:</i> ${data[entry].id}</p>
                  </div>
                  <div class="col-3">
                    <p><i>Order:</i> ${data[entry].order}</p>
                  </div>
                  <div class="col-6">
                    <p><i>Closest relative:</i> ${data[entry].closest_relative}</p>
                  </div>
                </div>
              </div>
            `);
          }

          const MAX_PAGE = 7;
          // count how may page is needed 
          var pageNum = Math.ceil(data.length / PER_PAGE);
          // url: page exceed
          if (page > pageNum || page <= 0) {
            $('#dataList').append(`
              <div class="list-group-item">
                <p><strong>
                  Unexpected url encontered
                </p></strong>
              </div>
            `);
            return;
          }
          // generate pagination
          var pageStart = 1;
          if (pageNum <= MAX_PAGE) {
            for (i=1; i<= pageNum; i++) {
              $('#nextPage').before(`
                <li class="page-item">
                  <a class="page-link" href="#">${i}</a>
                </li>
              `);
            }
          } else {
            if (page < MAX_PAGE) {
              pageStart = 1;
            } else if (pageNum - page < MAX_PAGE-1) {
              pageStart = pageNum - MAX_PAGE + 1;
            } else {
              pageStart = page - Math.floor(MAX_PAGE/2);
            }
            for (i=pageStart; i<pageStart+MAX_PAGE; i++) {
              $('#nextPage').before(`
                <li class="page-item">
                  <a class="page-link" href="#">${i}</a>
                </li>
              `);
            }
          }
          // assign active page item
          // note first 2 are non-numeric
          $(`#pagination>.page-item:nth-child(${page-pageStart+3})`).addClass('active');
          // disable previous or next link if needed
          if (page == 1) {
            $('#prevPage').addClass('disabled');
            $('#firstPage').addClass('disabled');
          }
          if (page == pageNum) {
            $('#nextPage').addClass('disabled');
            $('#lastPage').addClass('disabled');
          }

          // add link for every pagination 
          $('#pagination .page-link').click(function() {
            console.log('pagelink clicked');
            if ($(this).html() == '«') {
              var pageTo = 1;
            } else if ($(this).html() == '»') {
              var pageTo = pageNum;
            } else if ($(this).html() == '&lt;') {
              var pageTo = page - 1;
            } else if ($(this).html() == '&gt;') {
              var pageTo = page + 1;
            } else {
              var pageTo = parseInt($(this).html());
            }
            $.redirect('/advSearch/'+pageTo, postData, 'POST');
          });
        }
      });
    });
  </script>
@endsection
