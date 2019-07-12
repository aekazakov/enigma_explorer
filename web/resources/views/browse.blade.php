@extends('template')

@section('title', 'Browse')

@section('content')
<div class="row my-4">
  <div class="col-lg-12">
    <h1>By Orders</h1>
    <hr />
  </div>
  <div class="col-lg-12 text-left" id="orderData"></div>
</div>
<div class="row my-4">
  <div class="col-lg-12">
    <h1>By Genera</h1>
    <hr />
  </div>
  <div class="col-lg-12 text-left" id="genusData"></div>
  <div class="col-lg-12"><hr /></div>
</div>
<script>
  // fetch orders and genus data
  function fetchList(dataView, url, startC, endC) {
    $.ajax({
      url: url,
      success: function(data) {
        // color of tags will randomly vary within a range
        for (let key in data) {
          factor = Math.random();
          mcolor = [];
          for (let i=0; i<3; i++) {
            mcolor.push(startC[i] + (endC[i]-startC[i])*factor);
          }
          var itemString = `
            <div style="background-color:${'rgb('+mcolor.join(',')+')'}" class="myitems btn btn-light inline-block my-2 mx-4">
              <a class="text-white" href="/search?keyword=${key}">${key}</a>
              <span class="badge badge-light">${data[key]}</span>
            </div>`;
          $(dataView).append(itemString);
        }
        // remove marginal margins
        $(dataView + '>div:first-child').css('margin-left', '0px');
        $(dataView + '>div:last-child').css('margin-right', '0px');
      },
      error: function() {
        var errorString = `
          <p class="bg-danger">Unexpected server error. Please try again.</p>`;
        $(dataView).append(errorString);
      }
    });
  }

  fetchList('#orderData', '/api/v1/isolates/orders', [85, 239, 196], [0, 184, 148]);
  fetchList('#genusData', '/api/v1/isolates/genera', [116, 185, 255], [9, 132, 227]);
</script>
@endsection
