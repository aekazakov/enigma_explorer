// Define the blast object
let createBlastObj = function(id, tColor, blastDb) {
  // get component parameters
  var obj = new Object();
  obj.divId = id;
  obj.tColor = tColor;
  obj.blastDb = blastDb;
  // This request is fast. No need to request ASA page loaded
  obj.fetchBlast = function(info) {
    // info can be id of an isolate or a sequence
    let url, qData, type;
    if (/^\d+$/.test(info)) {
      url = '/api/v1/ncbi/blast/' + this.blastDb + '/' + info;
      qData = '';
      type = 'GET';
    } else {
      url = '/api/v1/ncbi/blast/' + this.blastDb;
      qData = info;
      type = 'POST';
      console.log(url, qData);
    }
    $.ajax({
      url: url,
      type: type,
      data: qData,
      success: (data) => {
        // check if there is any hit
        if ('message' in data) {
          console.log('No hits found');
          let errorString = '<h4 class="text-info">Not hits found above E value threshold!</h4>';
          $('#'+this.divId+'>.g-error').append(errorString);
          $('#'+this.divId+'>.g-loading').addClass('d-none');
          $('#'+this.divId+'>.g-hint').addClass('d-none');
          return;
        }
        let headStr = `
          <thead>
            <th>Isolate description</th>
            <th>% Identity</th>
            <th>% Coverage</th>
            <th>E value</th>
            <th>Alignment</th>
          </thead>`;
        $('#' + this.divId + '>table').prepend(headStr);
        LEN_PER_LINE = 60;
        for (hit of data) {
          hit.identity *= 100;    //convert to percentage
          hit.coverage *= 100;
          let trStr = `
            <tr>
              <th><a class="text-dark" href="#non-existing" id="a-${hit.isoid}">${hit.isoid} ${hit.title}</th>
              <td>${hit.identity.toFixed(4)}</td>
              <td>${hit.coverage.toFixed(4)}</td>
              <td>${hit.evalue.toFixed(2)}</td>
              <td>
                <a href="#"><span class="badge badge-pill badge-${this.tColor}"><span class="fa fa-chevron-down"></span> Show Align</span></a>
              </td>
            </tr>
            <tr class="collapse">
              <td colspan="5" class="alignBox"></td>
            </tr>
            `;
          $('#'+this.divId+' tbody').append(trStr);
          // get the link href for isos
          $.get('/api/v1/isolates/isoid/'+hit.isoid, (data) => {
            // assuming isoid is unique
            $('#'+this.divId+' #a-'+data.isolate_id).attr('href', '/isolates/id/'+data.id);
          });
          let al = hit.align.qseq.length;
          for (let i = 0; i < Math.floor(al / LEN_PER_LINE); i++) {
            // color the mismatch bp
            let qseq, midline, hseq;
            [qseq, hseq] = ['', ''];
            for (let j = 0; j < LEN_PER_LINE; j++) {
              // this is not optimal with a poor alignment
              if (hit.align.midline[i*LEN_PER_LINE+j] != '|') {
                qseq += '<span class="text-danger">'+hit.align.qseq[i*LEN_PER_LINE+j]+'</span>';
                hseq += '<span class="text-danger">'+hit.align.hseq[i*LEN_PER_LINE+j]+'</span>';
              } else {
                qseq += hit.align.qseq[i*LEN_PER_LINE+j];
                hseq += hit.align.hseq[i*LEN_PER_LINE+j];
              }
            }
            // continual spaces are omitted. convert to char entity
            midline = hit.align.midline.slice(i * LEN_PER_LINE, (i+1) * LEN_PER_LINE).replace(/ /g, '&nbsp;');
            $('#'+this.divId+' tbody>tr:last-child>.alignBox').append(qseq + '<br />' + midline+ '<br />' + hseq + '<br />');
          }
          // color the mismatch bp
          let qseq, midline, hseq;
          [qseq, hseq] = ['', ''];
          for (let j = 0; j < al % LEN_PER_LINE; j++) {
            if (hit.align.midline[al-al%LEN_PER_LINE+j] != '|') {
              qseq += '<span class="text-danger">'+hit.align.qseq[al-al%LEN_PER_LINE+j]+'</span>';
              hseq += '<span class="text-danger">'+hit.align.hseq[al-al%LEN_PER_LINE+j]+'</span>';
            } else {
              qseq += hit.align.qseq[al-al%LEN_PER_LINE+j];
              hseq += hit.align.hseq[al-al%LEN_PER_LINE+j];
            }
          }
          midline = hit.align.midline.slice(al-al%LEN_PER_LINE, al).replace(/ /g, '&nbsp;');
          $('#'+this.divId+' tbody>tr:last-child>.alignBox').append(qseq + '<br />' + midline+ '<br />' + hseq + '<br />');
        }
        // remove loading icon
        $('#'+this.divId+'>.g-loading ').addClass('d-none');
        // expand alignment
        $('#'+this.divId+' tbody td:last-child>a').click(function() {
          $(this).parents('tr').next('tr').toggleClass('show');
          return false;
        });
      },
      error: () => {
        console.log('ajax failed');
        let errorString = '<p class="bg-danger">Unexpected server error encountered.</p>';
        $('#'+this.divId+'>.g-error').append(errorString);
        $('#'+this.divId+'>.g-loading').addClass('d-none');
      }
    });
  }

  return obj;
};
