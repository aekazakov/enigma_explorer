@extends('template')

@section('title', 'Browse')

@section('content')
<!-- <div class="row my-4 d-none">
  <div class="col-lg-12">
    <h1>By Orders</h1>
    <hr />
  </div>
  <div class="col-lg-12 text-left" id="orderData"></div>
</div>
<div class="row my-4 d-none">
  <div class="col-lg-12">
    <h1>By Genera</h1>
    <hr />
  </div>
  <div class="col-lg-12 text-left" id="genusData"></div>
  <div class="col-lg-12"><hr /></div>
</div> -->
<div class="row my-4">
  <div class="col-lg-12">
    <table class="table" id="taxaDataView">
      <thead>
        <th>#</th>
        <th> </th>
        <th>Taxon</th>
        <th>#</th>
        <th>Select</th>
      </thead>
    </table>
  </div>
</div>
<script>
  // fetch orders and genus data
  /*function fetchList(dataView, url, startC, endC) {
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
  }*/

  function fetchTaxa() {
    $.ajax({
      url: "/api/v1/isolates/taxa",
      success: function(data) {
        for (let order in data) {
          // Build order string. The group of genus will be in tbody named $order
          let orderString = `
            <tbody id="row_${order}">
              <tr class="orderRow">
              <!-- order level text will be bigger -->
                <!-- Below pr-4 compensates the pl-4 indent in genus level to avoid jitter -->
                <th class="pr-4"><a class="text-dark" data-toggle="collapse" href="#${order}"><span class="fa fa-chevron-down"></span></a></th>
                <td class="pr-4"><a class="text-dark" href="/search?keyword=${order}">${order}</a></td>
                <td><span class="text-secondary">order</span></td>
                <td>
                  <span class="badge badge-info">${data[order].nGenera}</span>
                  <span class="badge badge-success">${data[order].tSpecies}</span>
                  <!-- Blue for #genus and green for #species -->
                </td>
                <td>
                  <div class="form-check-inline form-check">
                    <input class="form-check-input" type="checkbox" name="fakeRadio" value="fakeRadio" />
                  </div>
                </td>
              </tr>
            </tbody>`;
          $('#taxaDataView').append(orderString);
          // Build genus string by ele.genera
          // all genus are grouped by a tbody named by the order
          let genusGroupString = `
            <tbody class="collapse" id="${order}"></tbody>`;
          $('#taxaDataView').append(genusGroupString);
          // Iterate all genus
          let cGenera = data[order].genera;
          for (let genus in cGenera) {
            let genusString = `
              <tr class="bg-light" id="${genus}">
                <th class="pl-4"><a class="text-dark" data-toggle="collapse" href=".${genus}"><span class="fa fa-chevron-down"></span></a></th>
                <td class="pl-4"><em><a class="text-dark" href="/search?keyword=${genus}">${genus}</a></em></td>
                <td><span class="text-secondary">genus</span></td>
                <td><span class="badge badge-success">${cGenera[genus]}</span></td>
                <td>
                  <div class="form-check-inline form-check">
                    <input class="form-check-input" type="checkbox" name="fakeRadio" value="fakeRadio" />
                  </div>
                </td>
              </tr>`;
            $('#' + order).append(genusString);
          }

          // Hook to load species
          // Notice the lazy loading here: only when order is expanded the species will be loaded
          $('#row_'+order).click(function() {
            console.log(order+' clicked!');
            for (let genus in cGenera) {
              fetchSpecies(genus);
            }
            $(this).unbind();
          });
        }
      },
      error: function() {
        var errorString = `
          <p class="bg-danger">Unexpected server error. Please try again.</p>`;
        $('#taxaDataView').append(errorString);
      }
    });
  }

  function fetchSpecies(genus) {
    // Fetch isolates from given genus
    // Assume a <tr> named by the genus already exists
    if ($('#'+genus).length > 0) {
      $.ajax({
        url: "/api/v1/isolates/genus/" + genus,
        success: function(data) {
          // Iterate all isolates, reversed
          for (let i=data.length-1; i>=0; i--) {
            // Build species string, should have class of genus
            let species = data[i];
            let speciesString = `
              <tr class="collapse speciesRow ${genus}">
                <th></th>
                <td class="pl-4"><a class="text-dark" href="/isolates/id/${species.id}">${species.isolate_id}</td>
                <td></td>
                <td><em>${species.closest_relative}</em></td>
                <td>
                  <div class="form-check-inline form-check">
                    <input class="form-check-input" type="checkbox" name="fakeRadio" value="fakeRadio" />
                  </div>
                </td>
              </tr>`;
            // Notice the genus level has id of $genus
            $('#'+genus).after(speciesString);
          }
        },
        error: function() {
          var errorString = `
            <p class="bg-danger">Unexpected server error. Please try again.</p>`;
          $('#'+genus).append(errorString);
        }
      });
    } else {
      // Force log error if genus does not exist
      console.log('<tr> ' + genus + 'does not exist. Invalid append of isolates');
    }
  }

  $(document).ready(function() {
    fetchTaxa();
    //fetchList('#orderData', '/api/v1/isolates/orders', [85, 239, 196], [0, 184, 148]);
    //fetchList('#genusData', '/api/v1/isolates/genera', [116, 185, 255], [9, 132, 227]);
  });
</script>
@endsection
