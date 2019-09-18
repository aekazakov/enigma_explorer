@extends('template')

@section('title', 'Growth Curve')

@section('content')
  <div class="row">
    <div class="col-12 my-2">
      <h3>Metadata</h3>
      <hr />
    </div>
  </div>
  <div class="row">
    <div class="col-12 mb-4 mt-2">
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
          <tr>
            <th><span class="badge badge-pill badge-primary">Morgan's Plates Viewer</span></th>
            <td><a href="http://mprice.dev.microbesonline.org/cgi-bin/Fitness/plate_overview.pl?growth_plate={{ $id }}">Link</a></td>
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
    <div id="plateView" class="border border-dark col-12 col-md-8 col-lg-6 mx-auto m-4">
    </div>
    <div class="col-12 col-lg-6 my-4 mx-auto">
      <table id="wellDataView" class="table table-sm table-stripped">
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
    </div>
  </div>
  <div class="row">
    <div class="col-12 my-2">
      <h3>Visualizations</h3>
      <hr />
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <ul class="nav nav-tabs" id="viewControls" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="conditionsLink" data-toggle="tab" href="#conditionsView" role="tab">Conditions</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="heatmapLink" data-toggle="tab" href="#heatmapView" role="tab">Heatmap</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="curvesLink" data-toggle="tab" href="#curvesViewWrapper" role="tab">Curves</a>
        </li>
      </ul>
    </div>
  </div>
  <div class="row tab-content">
    <div class="tab-pane fade show active col-12 my-4" id="conditionsView" role="tabpanel">
      <div class="row overflow-auto">
        <div class="col-12" id="conditionsView-canvas">
          <table class="table table-sm table-borderless">
            <tbody></tbody>
          </table>
          <div class="row" id="legends"></div>
        </div>
      </div>
      <div class="row border rounded my-2">
        <form class="col-12 my-2">
          <h4 class="my-4">Configuration</h4>
          <div class="row">
            <div class="form-group col-12 col-md-4">
              <input type="checkbox" data-toggle="toggle" id="grdToggle" checked />
              <label class="mx-2">
                Show Gradients
              </label>
            </div>
            <div class="form-group col-12 col-md-4">
              <input type="checkbox" data-toggle="toggle" id="condToggle" />
              <label class="mx-2">
                Show Condition Texts
              </label>
            </div>
            <!-- Download image or HTML buttons -->
            <div class="col-12 col-md-4">
              <div class="row form-group">
                <div class="col-6">
                  <a class="btn btn-outline-success" id="cond-img-btn" role="button" href="#" download="Conditions_table">Download Image</a>
                </div>
                <div class="col-6">
                  <a class="btn btn-outline-success" id="cond-html-btn" role="button" href="#">Download HTML</a>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="tab-pane fade col-12 my-4" id="heatmapView" role="tabpanel">
      <div class="row overflow-auto">
        <div class="col-12" id="heatmapView-canvas">
          <table class="table table-sm table-borderless">
            <tbody></tbody>
          </table>
          <div class="row">
            <div class="col-12 my-3">
              <h5 id="timeLabel" class="text-center"></h5>
            </div>
          </div>
        </div>
      </div>
      <div class="row border rounded my-2">
        <form class="col-12 my-2">
          <h4 class="my-4">Configuration</h4>
          <div class="row">
            <div class="form-group col-12 col-md-4">
              <input type="checkbox" data-toggle="toggle" id="valuesToggle" checked />
              <label class="mx-2">
                Show values
              </label>
            </div>
            <div class="col-12 col-md-4"><!-- takes place --></div>
            <div class="col-12 col-md-4">
              <div class="row form-group">
                <div class="col-6">
                  <a class="btn btn-outline-success" id="hm-img-btn" role="button" href="#" download="">Download Image</a>
                </div>
                <div class="col-6">
                  <a class="btn btn-outline-success" id="hm-html-btn" role="button" href="#">Download HTML</a>
                </div>
              </div>
            </div>
            <div class="form-group col-12">
              <label>Time (min)</label>
              <input type="range" class="form-control-range" id="timeInput" value="0" />
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="tab-pane fade col-12 my-4" id="curvesViewWrapper" role="tabpanel">
      <div class="row">
        <div class="col-12" id="curvesView"></div>
      </div>
      <div class="row border rounded my-2">
        <form class="col-12 my-2">
          <h4 class="my-4">Configuration</h4>
          <div class="row">
            <p class="col-12">
              <strong>Hints:</strong><br />
              Use the above plate panel to select a set of wells for plotting. Crtl for addition and Shift for substraction.<br />
              Double click on a legend to show that one along.<br />
              Single click on a legend to hide the curve.<br />
              Scroll on the canvas for propotional zoom. Scoll on the axes for axis-specific zoom.<br />
              Mouse drag to move the canvas. Shift + drap to select a region.
              Double click on the canvas to restore default view.
            </p>
          </div>
          <div class="row">
            <div class="form-group col-12 col-md-6">
              <label for="condSelector">Select a condition</label>
              <select id="condSelector" class="custom-select">
                <option selected value="0">--Customized selection--</option>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- For downloading png -->
    <div class="d-none" id="downloadTmp"></div>
    <!-- <div class="col-12 mx-4">
      <button type="button" role="button" id="clearPlotBtn" class="btn btn-warning w-100">Clear Plots</button>
    </div> -->
  </div>

  <!-- Bootstrap toggle switch -->
  <script src="/js/bootstrap-toggle.min.js"></script>
  <!-- Plotting utilities -->
  <script src="/js/plotly.min.js"></script>
  <!-- Google Palette -->
  <script src="/js/palette.js"></script>
  <!-- Drag Select -->
  <script src="/js/ds.min.js"></script>
  <!-- Convert DOM to canvas -->
  <script src="/js/html2canvas.min.js"></script>
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
      // make the 384 plate bigger
      if (nWells == 384) {
        $('#plateView').removeClass('col-md-8').removeClass('col-lg-6');
      }
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
         <div class="text-center col p-0 noselect">${j+1}</div>
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
          <div class="col-1 text-center p-0 mx-2 noselect">${rowLabel}</div>
        `);

        for (let j = 0; j < nCol; j++) {
          let loc = rowLabel+(j+1);
          let colString = `
            <button data-locInt="${i*nCol+j+1}" data-loc="${rowLabel+(j<9?'0':'')+(j+1)}"class="col plateCol rounded-circle border border-dark p-0 m-1"></button>
          `;
          $('#plateView').children('.plateRow').eq(i).append(colString);
        }
      }

      // set square grid
      $('.plateCol').each(function() {
        $(this).css('height', $(this).css('width'));
        // respond when resize
        $(window).resize(() => {
          $(this).css('height', $(this).css('width'));
        });
      });

      // trigger get well data
      $('#wellDataView').trigger('render');
    };

    var eraseWellSelected = function(element) {
      $(element).removeClass('bg-info');
      // also highlight col and row
      let rowLabel = $(element).attr('data-loc')[0];
      let colLabel = $(element).attr('data-loc').slice(1);
      // if selected col or row changes, remove styles
      let locs = [];
      $(this.getSelection()).each(function() {
        locs.push($(this).attr('data-loc'));
      });
      let rowLbs = []; let colLbs = [];
      for (loc of locs) {
        rowLbs.push(loc[0]);
        colLbs.push(loc.slice(1));
      }
      if (!rowLbs.includes(rowLabel)) {
        $('.plateRow').children('div:contains('+rowLabel+')').removeClass('text-white').removeClass('bg-danger');
      }
      if (!colLbs.includes(colLabel)) {
        $('#plateColLabel').children().eq(parseInt(colLabel)).removeClass('text-white').removeClass('bg-danger');
      }
    }

    var onWellSelected = function(element) {    // Notice this is used. Cannot use arrow function
      $(element).addClass('bg-info');
      // also highlight col and row
      let rowLabel = $(element).attr('data-loc')[0];
      let colLabel = $(element).attr('data-loc').slice(1);
      $('#plateColLabel').children().eq(parseInt(colLabel)).addClass('text-white').addClass('bg-danger');
      $('.plateRow').children('div:contains('+rowLabel+')').addClass('text-white').addClass('bg-danger');

      // feed data to well data view
      let idx = $(element).attr('data-locInt') - 1;
      let well = wellData(idx);
      $('#wellLocD').html(well.wellLocation);
      $('#mediaD').html(well.media);
      $('#strainD').html(well.strainLabel);
      $('#treatmentD').html(well.treatment.condition + ' ' +
        well.treatment.concentration + ' ' +
        well.treatment.units);
    }

    var setPlateDS = function() {
      // the function is triggered only once after plate rendering
      plateDS = new DragSelect({
        selectables: $('.plateCol'),
        area: $('#plateView')[0],    // defines the dragable area, notice DOM element
        customStyles: false,
        onDragStart: () => {
          // set condition selector to empty
          $('#condSelector').prop('value', "0");
        },
        onElementUnselect: eraseWellSelected,
        onElementSelect: onWellSelected,
        callback: renderValues
      });

      // if touchable device, turn on multiple select
      if (window.mobileAndTabletcheck()) {
        plateDS.multiSelectMode = true;
      }

      // trigger selection on the first well
      $('.plateCol').eq(0).trigger('click');

      return plateDS;
    };
    
    // convert between hex and rgb
    function hexToRgb(hex) {
      var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
      } : null;
    }
    function rgbToHex(r, g, b) {
      return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    // make a gradient
    let colorFade = function(hexCol, ratio, limits, bottom=0.5) {
      // check if ratio is proper
      if (limits == undefined || ratio < limits[0] || ratio > limits[1]) return;

      let rgbCol = hexToRgb(hexCol);
      if (limits[0] == limits[1]) {    // Will cause divide by 0 error
        var grdCol = rgbCol;
      } else {
        var grdCol = {};
        for (key in rgbCol) {
          grdCol[key] = rgbCol[key] + (255 - rgbCol[key]) * (limits[1] - ratio) / (limits[1] - limits[0]) * (1-bottom);
        }
      }
      return rgbToHex(Math.round(grdCol.r), Math.round(grdCol.g), Math.round(grdCol.b));
    };

    var wellIdentifier = function(well) {
      /* 4 states: empty, blank, control and samples
      if there is neither treatment nor strain, then empty
      if there is strain but no treatment, then control
      if there is treatment but not strain, then blank
      if there is both treatment and strain, then sample*/

      if (well.strainLabel.toLowerCase() == 'none' && well.treatment.condition.toLowerCase() == 'none') {
        return 'Empty';
      } else if (well.strainLabel.toLowerCase() == 'none') {
        return 'Blank';
      } else if (well.treatment.condition.toLowerCase() == 'none') {
        return 'Control';
      } else {
        return `${well.strainLabel},${well.treatment.condition}`;
      }
    }

    var renderConditions = function(nWells, data, style='default', showCond=false) {
      let wellDef = { "24": [4,6], "48": [6,8], "96": [8,12], "384": [16,24] };

      let nRow = wellDef[nWells][0];
      let nCol = wellDef[nWells][1];

      // Assign the gradient map
      let grdMap = {};
      for (well of data) {
        var key = wellIdentifier(well);
        if (!grdMap.hasOwnProperty(key)) {
          grdMap[key] = [well.treatment.concentration];
        } else {
          grdMap[key].push(well.treatment.concentration);
        }
        
      }

      for (key in grdMap) {
        let minVal = Math.min(...grdMap[key]);
        let maxVal = Math.max(...grdMap[key]);
        grdMap[key] = [ minVal, maxVal ];
      }

      // Assign the color map
      let colNum = Object.keys(grdMap).length;
      if (colNum <= 8) {
        var colSeq = palette('cb-Dark2', colNum);
      } else {
        var colSeq = palette('tol-rainbow', colNum);
      }
      let colMap = {}; let k = 0;
      for (item of Object.keys(grdMap)) {
        colMap[item] = colSeq[k];
        k++;
      }

      // Closure colMap and grdMap
      plotPalette = (key) => {
        return [colMap[key], grdMap[key]];
      };

      // show legends
      $('#legends').html('');
      $('#legends').trigger('renderLegends', [colMap]);

      let cellStr = function(well, showCond=false) {
        var key = wellIdentifier(well);
        if (['Empty', 'Control', 'Blank'].includes(key)) {
          return key;
        }
        if (!showCond) {
          str = well.treatment.concentration+' '+well.treatment.units;
        } else {
          str = well.treatment.concentration+' '+well.treatment.units+'<br/>'+
            '<small>'+well.treatment.condition+'</small>';
        }
        return str;
      };

      // add cells
      if (style.toLowerCase() == 'default') {
        $('#conditionsView tbody').html('');
        for (let i = 0; i < nRow; i++) {
          $('#conditionsView tbody').append(`
            <tr class="text-center"><td>${String.fromCharCode(65+i)}</td></tr>
          `);
          for (let j = 0; j < nCol; j++) {
            let wellLoc = $('.plateCol').eq(i*nCol+j).attr('data-locInt');
            let well = data[parseInt(wellLoc)-1];
            $('#conditionsView tbody>tr:last-child').append(`
              <td class="condCol border border-dark">${cellStr(well, showCond)}</td>
            `);
            // set color for newly appended cell
            let key = wellIdentifier(well) == 'Empty' ? '' : wellIdentifier(well);    // There is a colormap for empty, but not shown
            $('#conditionsView tbody>tr:last-child>td:last-child').css('background-color',
              '#'+colMap[key]);
          }
        }
      } else if (style.toLowerCase() == 'gradient') {
        //console.log('gradient render called');
        //console.log(grdMap);
        $('#conditionsView tbody').html('');
        for (let i = 0; i < nRow; i++) {
          $('#conditionsView tbody').append(`
            <tr class="text-center"><td>${String.fromCharCode(65+i)}</td></tr>
          `);
          for (let j = 0; j < nCol; j++) {
            let wellLoc = $('.plateCol').eq(i*nCol+j).attr('data-locInt');
            let well = data[parseInt(wellLoc)-1];
            $('#conditionsView tbody>tr:last-child').append(`
              <td class="condCol border border-dark">${cellStr(well, showCond)}</td>
            `);
            // set color for newly appended cell
            let key = wellIdentifier(well) == 'Empty' ? '' : wellIdentifier(well);
            let cellCol = colMap[key];
            let limits = grdMap[key];
            let fadedCol = colorFade('#' + cellCol, well.treatment.concentration, limits)
            $('#conditionsView tbody>tr:last-child>td:last-child').css('background-color', fadedCol);
          }
        }
      }

      // append column labels
      $('#conditionsView tbody').prepend('<tr class="text-center"><td> </td></tr>');
      for (let i = 0; i < nCol; i++) {
        $('#conditionsView tbody>tr:first-child').append(`
          <td>${i+1}</td>
        `);
      }

      // update the download buttons
      $('#cond-img-btn').each(function() {
        // Set html to loading icon
        $(this).html('<span class="fas fa-spinner mx-4"></span>');
        let imgW = $('#conditionsView table').width();
        convertToImage($('#conditionsView-canvas')[0], this, imgW, undefined, 'Download Image');
        let plateId = "{{ $id }}";
        $(this).prop('download', 'conditions-table-'+plateId);
      });

      $('#cond-html-btn').each(function() {
        // Set html to loading icon
        $(this).html('<span class="fas fa-spinner mx-4"></span>');
        var htmlStr = $('#conditionsView>div:first-child').html();
        $(this).prop('href', 'data:text/html,' + encodeURIComponent(htmlStr), '_blank');
        let plateId = "{{ $id }}";
        $(this).prop('download', 'conditions-table-'+plateId+'.html');
        // set back
        $(this).html('Download HTML');
        return false;
      });
    };

    var renderHeatmap = function(nWells, data, timepoint, showNum = true) {
      let wellDef = { "24": [4,6], "48": [6,8], "96": [8,12], "384": [16,24] };

      let nRow = wellDef[nWells][0];
      let nCol = wellDef[nWells][1];

      let cellStr = function(well, tp, showNum=true) {
        if (!showNum) return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        else {
          if(well.strainLabel == 'None') {
            var str = 'Empty';    // Empty cells also values, might change in the future
          } else {
            str = parseFloat(well.data.values[tp]).toFixed(4);
          }
          return str;
        }
      };

      // add cells
      $('#heatmapView tbody').html('');
      let limits = [1.0, 0.0];
      for (let i = 0; i < nRow; i++) {
        for (let j = 0; j < nCol; j++) {
          let well = data[i*nCol+j];
          let minVal = Math.min(...well.data.values);
          let maxVal = Math.max(...well.data.values);
          if (minVal < limits[0]) limits[0] = minVal;
          if (maxVal > limits[1]) limits[1] = maxVal;
        }
      }
      for (let i = 0; i < nRow; i++) {
        $('#heatmapView tbody').append(`
          <tr class="text-center"><td>${String.fromCharCode(65+i)}</td></tr>
        `);
        for (let j = 0; j < nCol; j++) {
          let wellLoc = $('.plateCol').eq(i*nCol+j).attr('data-locInt');
          let well = data[parseInt(wellLoc)-1];
          $('#heatmapView tbody>tr:last-child').append(`
            <td class="condCol border border-dark">${cellStr(well, timepoint, showNum)}</td>
          `);
          // set color for newly appended cell
          $('#heatmapView tbody>tr:last-child>td:last-child').css('background-color',
            colorFade('#2980b9', well.data.values[timepoint], limits, 0.0));
        }
      }

      // append column labels
      $('#heatmapView tbody').prepend('<tr class="text-center"><td> </td></tr>');
      for (let i = 0; i < nCol; i++) {
        $('#heatmapView tbody>tr:first-child').append(`
          <td>${i+1}</td>
        `);
      }

      // also update time label
      let minutes = data[0].data.timepoints[timepoint] / 60;
      let hours = Math.round(minutes / 60);
      minutes = Math.floor(minutes % 60 * 10) / 10;
      $('#timeLabel').html(hours+' hours, '+minutes+' minutes')
      
      // When page is loaded, trigger timerInput change once
      $('#timeInput').trigger('mouseup');

    };

    var renderLegends = function(colMap) {
      for (let cond in colMap) {
        if (cond == 'None') {
          continue;
        }
        $('#legends').append(`
          <div class="col-md-3 col-6 my-2">
            <span style="background-color:#${colMap[cond]}">&nbsp;&nbsp;&nbsp;&nbsp;</span>
            <span>${cond}</span>
          </div>
        `);
      }
    };

    var getWellData = function() {
      $.ajax({
        url: '/api/v1/growth/wells/id/{{ $id }}',
        type: 'GET',
        success: (data) => {

          // Change the data-locInt of each well
          for (let i in data) {
            let wellLoc = data[i].wellLocation;
            $(`.plateCol[data-loc="${wellLoc}"]`).attr('data-locInt', parseInt(i)+1);
          }
          // Trigger rendering of conditions overview
          // Change of configuration trigger re-redenring
          $('#grdToggle,#condToggle').change(function() {
            let grdState = $('#grdToggle').prop('checked');
            let condState = $('#condToggle').prop('checked');
            $('#conditionsView').trigger('render', [data, grdState?'gradient':'default', condState]);
          });
          $('#grdToggle').trigger('change');
          // Set range input
          let maxTime = data[0].data.timepoints.length - 1;
          $('#timeInput').prop('min', "0").prop('max', maxTime);
          // Bind when the range input changes
          $('#timeInput').on('input', function() {
            let val = $('#timeInput').prop('value');
            let showValues = $('#valuesToggle').prop('checked');
            $('#heatmapView').trigger('render', [data, val, showValues]);
          })
          $('#valuesToggle').change(function() {
            $('#heatmapView').trigger('render', [data, $('#timeInput').prop('value'), $(this).prop('checked')]);
          })
          $('#valuesToggle').trigger('change');

          // return data in the form of closure
          wellData = function(idx) {
            return data[idx];
          }
          // Set drag select
          var plateDS = setPlateDS();

          // set up the condition select for curves
          (() => {
            let condMap = {};
            for (i in data) {
              let well = data[i];
              let key = wellIdentifier(well);
              if (!condMap.hasOwnProperty(key)) {
                condMap[key] = [];
              }
              condMap[key].push(i);
            }
            // add options to select
            for (key in condMap) {
              $('#condSelector').append(`
                <option value="${key}">${key}</option>
              `);
            }
            // condition selector selection event
            $('#condSelector').change(function() {
              let key = $(this).prop('value');
              let selectSet = [];
              for (i of condMap[key]) {
                selectSet.push($('.plateCol[data-locint="'+(parseInt(i)+1)+'"]')[0]);
              }
              plateDS.setSelection(selectSet);
              plateDS.callback();
            })
          })();

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

    var renderValues = function() {
      // Define the canvas
      let view = $('#curvesView');
      
      var plotData = [];    // for plotly
      var keys = [];
      $(this.getSelection()).each(function() {
        let idx = $(this).attr('data-locInt') -1;
        let well = wellData(idx);
        let wd = well.data;
        let wt = well.treatment;
        // re-scale timepoints
        let tp = [];
        for (let i = 0; i < wd.timepoints.length; i++) {
          tp.push(wd.timepoints[i] / 3600);
        }
        var key = wellIdentifier(well);
        if (!['Empty', 'Blank', 'Control'].includes(key)) {
          var nameStr = key+','+well.treatment.concentration+well.treatment.units;
        } else {
          var nameStr = key;
        }
        keys.push(nameStr);
        let [col, grd] = plotPalette(key);
        let fadedCol = colorFade('#' + col, wt.concentration, grd, 0.2)
        let trace = {
          x: tp,
          y: wd.values,
          name: nameStr,
          line: {
            color: fadedCol,
            width: 2
          }
        };
        plotData.push(trace);
      });

      // sort plotData according to keys
      /* plotDataSorted = [];
      keysSorted = Array.prototype.slice.call(keys).sort();    // keep a copy
      for (key of keysSorted) {
        plotDataSorted.push(plotData[keys.findIndex((ele) => {return ele == key})]);
      } */

      let layout = {
        showlegend: true,
        margin: {r: 0, t: 0, l: 50, b: 50},
        hovermode: false,
        dragmode: 'pan',
        xaxis: {
          title: 'Time (min)'
        },
        yaxis: {
          title: 'OD 600'
        }
       };

      let config = {
        responsive: true,
        displayModeBar: false,
        showHint: false,
        scrollZoom: true
      };

      Plotly.newPlot('curvesView', plotData, layout, config);    // empty layout

      // Also switch to curves tab by clicking the link
      $('#curvesLink').trigger('click');
    };

    var convertToImage = function(ele, hook, imgW, imgH, recStr) {
      let pos = $(ele).offset();
      imgW = imgW === undefined ? $(ele).width() : imgW;
      imgH = imgH === undefined ? $(ele).height() : imgH;
      // html2canvas cannot correctly detect the offset of ele after ele is rerended, thus munally designation is demanded
      html2canvas(ele, {
          x: pos.left,
          y: pos.top,
          width: imgW * 1.05,
          height: imgH * 1.05    // keep the edges safe
        }).then(function(canvas) {
          // $('.container').append(canvas);
          let dataPath = canvas.toDataURL('image/png');
          $(hook).attr('href', dataPath);
          $(hook).html(recStr);
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

      // render conditions overview
      $('#conditionsView').on('render', (event, data, style, cond) => {
        renderConditions(parseInt($('#nWellsD').html()), data, style, cond);
      });
      $('#legends').on('renderLegends', (event, colMap) => {
        renderLegends(colMap);
      });

      // render heatmap
      $('#heatmapView').on('render', (event, data, val, showNum) => {
        renderHeatmap(parseInt($('#nWellsD').html()), data, val, showNum);
      })

      // Set toggle switch to on
      $('#grdToggle').bootstrapToggle('on');

      // set initial size of plot canvas
      // canvas will be reset when switch to curves tab
      $('#curvesLink').click('click', () => {
        // automatically reset
        setTimeout(() => {
          Plotly.Plots.resize('curvesView');
        }, 100);
      });

      $('#clearPlotBtn').click(function() {
        Plotly.newPlot('curvesView');
      });

      // Register heatmap download btns
      $('#timeInput').change(function() {
        //console.log('mouse up fired');
        // update the download buttons
        $('#hm-img-btn').each(function() {
          // set to loading icon
          $(this).html('<span class="fas fa-spinner mx-4"></span>');
          // maunally pass the width of overflown table
          let imgW = $('#heatmapView table').width();
          convertToImage($('#heatmapView-canvas')[0], this, imgW, undefined, "Download Image");
          let plateId = "{{ $id }}";
          $(this).prop('download', 'heatmap-table-'+plateId);
        });
        $('#hm-html-btn').each(function() {
          // set to loading icon
          $(this).html('<span class="fas fa-spinner mx-4"></span>');

          var htmlStr = $('#heatmapView-canvas').html();
          $(this).prop('href', 'data:text/html,' + encodeURIComponent(htmlStr), '_blank');
          let plateId = "{{ $id }}";
          $(this).prop('download', 'heatmap-table-'+plateId+'.html');
          // set back
          $(this).html('Download HTML')
          return false;
        });
      });

    });
  </script>
@endsection
