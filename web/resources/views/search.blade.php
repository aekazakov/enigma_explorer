@extends('template')

@section('title')
Search: {{ $tag }}
@endsection

@section('content')
  <div class="row my-4">
    <h3>Search results</h3>
  </div>
  <div class="row">
    <p><span id="resNumber"></span> <strong>Results</strong></p>
  </div>
  <div class="row my-4">
    <div class="col">
      <ul class="list-group-flush" id="dataList">
      </ul>
    </div>
  <div>
  </div>
  <script>
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
    
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
    
            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
    };

    $(document).ready(function() {
      // get number of results
      $.get('/api/v1/isolates/count/'+getUrlParameter('keyword'), function(data) {
        $('#resNumber').html(data.count)
      });

      // get list of isolates
      $.ajax({
        url: '/api/v1/isolates/keyword/'+getUrlParameter('keyword'),
        success: function(data) {
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
          for (entry in data) {
            $('#dataList').append(`
              <div class="list-group-item">
                <div class="row">
                  <div class="col">
                    <p><strong>
                      <a href="/select/${data[entry].isolate_id}">${data[entry].isolate_id}</a>
                    </strong></p>
                  </div>
                </div>
                <div class="row my-2">
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
        },
        error: function(jqXHR, text, code) {
          if (jqXHR.status == 400) {
            var errorHint = "Something went wrong :( Please check your keyword.";
          } else {
            var errorHint = "Unknown error. Please contact admin!";
          }
          $('#resNumber').html('0');
          $('#dataList').append(`
            <div class="list-group-item">
              <p><strong>
                ${errorHint}
              </p></strong>
            </div>
          `);
        }
      });
    });
  </script>
@endsection
