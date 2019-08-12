@extends('template')

@section('title', 'Growth Curve')

@section('content')
  <div class="row">
    <div class="col-12 my-4">
      <table id="metaDataView" class="table table-sm table-striped">
        <tbody>
          <tr>
            <th><span class="badge badge-pill badge-primary">Plate Type</span></th>
            <td id="plateTypeD"></td>
            <th><span class="badge badge-pill badge-primary">Number of Wells</span></th>
            <td id="nWellsD"></td>
          </tr>
          <tr>
            <th><span class="badge badge-pill badge-primary">Date Created<span></th>
            <td id="dCreatedD"></td>
            <th><span class="badge badge-pill badge-primary">Date Scanned</span></th>
            <td id="dScannedD"></td>
          </tr>
          <tr>
            <th><span class="badge badge-pill badge-primary">Instrument<span></th>
            <td id="instrumentD" colspan="3"></td>
          </tr>
          <tr>
            <th><span class="badge badge-pill badge-primary">Anaerobic<span></th>
            <td id="anaerobicD"></td>
            <th><span class="badge badge-pill badge-primary">Measurement</span></th>
            <td id="measureD"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-12 my-2">
      <h3>Plate Overview</h3>
      <hr />
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md-9 col-lg-4 mx-auto m-4 p-0">
      <div class="row"><div id="plateView" class="col border border-dark"></div></div>
    </div>
    <div class="col-12 col-lg-8 my-4">
      <table id="wellDataView" class="table table-sm table-stripped mx-4">
        <thead>
          <tr>
            <th>Plate Location</th>
            <th id="wellLocD"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th><span class="badge badge-pill badge-info">Media</span></th>
            <td id="mediaD"></td>
          </tr>
          <tr>
            <th><span class="badge badge-pill badge-info">Strain</span></th>
            <td id="strainD"></td>
          </tr>
          <tr>
            <th><span class="badge badge-pill badge-info">Treatment</span></th>
            <td id="treatmentD"></td>
          </tr>
        </tbody>
      </table> 
      <!-- An individual row is added to prevent plateView expand -->
      <div class="row">
        <div class="col-12 my-4" id="valuesView"></div>
        <div class="col-12 mx-4">
          <button type="button" role="button" id="clearPlotBtn" class="btn btn-warning w-100">Clear Plots</button>
        </div>
      </div>
    </div>
  </div>

  <script src="/js/plotly.min.js"></script>
  <script>
    var getMeta = function() {
      $.ajax({
        url: '/api/v1/growth/meta/id/{{ $id }}',
        type: 'GET',
        success: (data) => {
          $('#plateTypeD').html(data.plateType);
          $('#nWellsD').html(data.numberOfWells);
          $('#dCreatedD').html(data.dateCreated);
          if (Date.parse(data.dateScanned)) {
            $('#dScannedD').html(data.dateScanned);
          } else {
            // 0000-00-00 00:00:00 returns NaN
            $('#dScannedD').html('No Value');
          }
          $('#instrumentD').html(data.instrumentName);
          $('#anaerobicD').html(data.anaerobic);
          if (data.measurement) {
            $('#measureD').html(data.measurement);
          } else {
            $('#measureD').html('No Value');
          }
      
          // Trigger plate rendering
          $('#plateView').trigger('render');
        },
        error: (xhr, status, err) => {
          if (xhr.status == '400') {
            $('#metaDataView').before(`
              <p class="bg-danger">Invalid plate id encountered!</p>
            `);
          } else {
            $('#metaDataView').before(`
              <p class="bg-danger">Unexpected server-side error occured!</p>
            `);
          }
        }
      });
    };

    var renderPlate = function(nWells) {
      let wellDef = { "24": [4,6], "48": [6,8], "96": [8,12], "384": [16,24] };

      let nRow = wellDef[nWells][0];
      let nCol = wellDef[nWells][1];

      // add plate column label
      let colLabelString = `
        <div class="row my-2" id="plateColLabel">
          <div class="col-1 mx-2"></div>
        </div>
      `;
      $('#plateView').append(colLabelString);
      for (let j = 0; j < nCol; j++) {
        $('#plateColLabel').append(`
         <div class="text-center col p-0">${j+1}</div>
        `);
      }

      for (let i = 0; i < nRow; i++) {
        let rowString = `
          <div class="row plateRow p-0"></div>
        `;
        $('#plateView').append(rowString);
        // add row label
        let rowLabel = String.fromCharCode(65+i);
        $('#plateView').children('.plateRow').eq(i).append(`
          <div class="col-1 text-center p-0 my-1 mx-2">${rowLabel}</div>
        `);

        for (let j = 0; j < nCol; j++) {
          let colString = `
            <div data-locInt="${i*nCol+j+1}" data-loc="${rowLabel+(j+1)}"class="col plateCol rounded-circle border border-dark p-0 m-1"></div>
          `;
          $('#plateView').children('.plateRow').eq(i).append(colString);
        }
      }

      // set square grid
      $('.plateCol').each(function() {
        $(this).css('height', $(this).css('width'));
      });

      // well click event
      $('.plateCol').click(function() {
        $('.plateCol').removeClass('bg-info');
        $(this).addClass('bg-info');
        $('.plateRow>div,#plateColLabel>div').removeClass('text-white').removeClass('bg-danger');
        // also highlight col and row
        let rowLabel = $(this).attr('data-loc')[0];
        let colLabel = $(this).attr('data-loc').slice(1);
        $('#plateColLabel').children().eq(parseInt(colLabel)).addClass('text-white').addClass('bg-danger');
        $('.plateRow').children('div:contains('+rowLabel+')').addClass('text-white').addClass('bg-danger');
      });

      // trigger get well data
      $('#wellDataView').trigger('render');
    };

    var getWellData = function() {
      $.ajax({
        url: '/api/v1/growth/wells/id/{{ $id }}',
        type: 'GET',
        success: (data) => {
          // plateView should already there
          $('.plateCol').click(function() {
            let idx = $(this).attr('data-locInt');
            let well = data[idx];
            $('#wellLocD').html(well.wellLocation);
            $('#mediaD').html(well.media);
            $('#strainD').html(well.strainLabel);
            $('#treatmentD').html(well.treatment.condition + ' ' +
              well.treatment.concentration + ' ' +
              well.treatment.units);
            // also render the plot
            renderValues(well.data);
          });
          $('.plateCol').first().trigger('click');
        },
        error: (xhr, status, err) => {
          if (xhr.status == '400') {
            $('#wellDataView').before(`
              <p class="bg-danger">Invalid plate id encountered!</p>
            `);
          } else {
            $('#wellDataView').before(`
              <p class="bg-danger">Unexpected server-side error occured!</p>
            `);
          }
        }
      });
    };

    var renderValues = function(data) {
      // Define the canvas
      let view = $('#valuesView');
      $(view).css('height', $(view).css('width') * 0.6);
      // re-scale timepoints
      for (let i = 0; i < data.timepoints.length; i++) {
        data.timepoints[i] /= 3600;
      }
      Plotly.plot($(view)[0], [{
        x: data.timepoints,
        y: data.values
      }], {
        margin: { t: 0 }
      });
    };

    $(document).ready(function() {
      // retrieve plate metadata
      getMeta();

      // render plate graphics
      $('#plateView').one('render', function() {
        renderPlate(parseInt($('#nWellsD').html()));
      });

      // render well meta and plots
      $('#wellDataView').one('render', function() {
        getWellData();
      });

      $('#clearPlotBtn').click(function() {
        Plotly.newPlot('valuesView');
      });
    });
  </script>
@endsection
