@extends('template')

@section('title', 'Detail')

@section('content')
  <div class="row">
    <div class="col-sm-12">
    <div class="sk-rotating-plane" id="loadingIcon"></div>
    <h1 class="h1 my-4" id="isoidInfo"></h1>
    <table class="table table-sm table-striped">
      <tbody>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Isolation Condition</span></h5></th>
          <td class="align-middle" id="conditionInfo" colspan="3"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Order</span></h5></th>
          <td class="align-middle" id="orderInfo" colspan="3"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Closest Relative in NCBI</span></h5></th>
          <td class="align-middle" id="relativeInfo"></td>
          <td><h5><span class="badge badge-pill badge-secondary">% Similarity</span></h5></td>
          <td class="align-middle" id="similarityInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Date Sampled</span></h5></th>
          <td class="align-middle" id="dateInfo" colspan="3"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Well/Sample ID</span></h5></th>
          <td class="align-middle" id="sampleidInfo" colspan="3"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Lab Isolated</span></h5></th>
          <td class="align-middle" id="labInfo" colspan="3"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Campaign or Set</span></h5></th>
          <td class="align-middle" id="campaignInfo" colspan="3"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Morgan's Fitness Browser</span></h5></th>
          <td class="align-middle" id="fitness-link" colspan="3">Not Available</td>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12">
    <h3 class="h3">Find relatives</h3>
    <hr />
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <p class="text-muted my-1">With NCBI Bacteria & Archaea 16S rRNA</p>
      <button id="matchButton" class="btn btn-outline-success mr-2 my-2 g-button" data-target="#genomeTable" hide-target="#lbTable,#silvaTable,#ncbiTable" type="button">Match name</button>
      <button id="ncbiButton" class="btn btn-outline-success mx-2 my-2 g-button" data-target="#ncbiTable" hide-target="#lbTable,#silvaTable,#genomeTable" type="button">BLAST</button>
    </div>
    <div class="col-md-4">
      <p class="text-muted my-1">With <a href="https://www.arb-silva.de">SILVA</a> SSU dataset</p>
      <button id="silvaButton" class="btn btn-outline-warning my-2 g-button" data-target="#silvaTable" hide-target="#lbTable,#genomeTable,#ncbiTable" type="button">BLAST</button>
    </div>
    <div class="col-md-4">
      <p class="text-muted my-1">With ENIGMA isolates 16s rRNA</p>
      <button id="lbButton" class="btn btn-outline-primary my-2 g-button" data-target="#lbTable" hide-target="#genomeTable,#silvaTable,#ncbiTable" type="button">BLAST</button>
    </div>
  </div>
  <div class="row my-3">
    <div class="col-sm-12">
    <div id="genomeCollapse" class="collapse">
      <div class="card card-body">
        <div id="genomeTable" class="d-none">
          <div class="g-hint my-4">
            <p class="small">
            The program trys to find genomes within NCBI genome database which belongs to the organism same as the one annotated as "closet Relative in NCBI" of the current isolate. Only genome sequence, other than 16s rRNA nor metagenomic sequences, will be found.<br />
            The queries to NCBI is performed via <a href="https://www.ncbi.nlm.nih.gov/books/NBK25497/">Entrez Programming Utilities</a>. Due to NCBI restrictions, no more than 3 requests can be posted to NCBI server per second, which slows down the program. Thus, hits are limited to 10 arbitrary genomes among all possible related genomes.
            </p>
          </div>
          <div class="g-loading sk-wave">
            <div class="sk-rect sk-rect1"></div>
            <div class="sk-rect sk-rect2"></div>
            <div class="sk-rect sk-rect3"></div>
            <div class="sk-rect sk-rect4"></div>
            <div class="sk-rect sk-rect5"></div>
          </div>
          <table class="table table-sm">
            <tbody>
            </tbody>
          </table>
          <div id="genomeError"></div>
        </div>
        @component('blastTable', [ 'id' => 'lbTable' ])
          <p class="small">
          The program performs a local BLAST against 16s rRNA sequences all ENIGMA isolates.</br>
          <strong>Identity</strong> is defined as (# of matched letters) / (length of query seq).<br />
          <strong>Coverage</strong> is defined as (length of alignement) / max((length of query seq), (length of alignment)).</br>
          Hits are sorted by identity. E value threshold: 1E-10. Hits below the threshold are filtered out. Showing 50 hits at a maximum.
          </p>
        @endcomponent
        @component('blastTable', [ 'id' => 'silvaTable' ])
          <p class="small">
          The program BLASTs 16s rRNA of the current isolate against <a href="https://www.arb-silva.de/projects/ssu-ref-nr/">SILVA SSU Ref NR 99</a> database.</br>
          <strong>Identity</strong> is defined as (# of matched letters) / (length of query seq).<br />
          <strong>Coverage</strong> is defined as (length of alignement) / max((length of query seq), (length of alignment)).</br>
          Hits are sorted by identity. E value threshold: 1E-10. Hits below the threshold are filtered out. Showing 50 hits at a maximum.
          </p>
        @endcomponent
        @component('blastTable', [ 'id' => 'ncbiTable' ])
          <p class="small">
          The program BLASTs 16s rRNA of the current isolate against NCBI 16SMicrobial database. See <a href="ftp://ftp.ncbi.nlm.nih.gov/blast/">NCBI FTP server</a> for more information.</br>
          <strong>Identity</strong> is defined as (# of matched letters) / (length of query seq).<br />
          <strong>Coverage</strong> is defined as (length of alignement) / max((length of query seq), (length of alignment)).</br>
          Hits are sorted by identity. E value threshold: 1E-10. Hits below the threshold are filtered out. Showing 50 hits at a maximum.
          </p>
        @endcomponent
        <p class="small text-muted mt-4">
        Need more information?<br />
        The button directs to NCBI BLAST website, BLASTing the 16s rRNA sequence of the current isolate against nr/nt (non-redundent nucleotide) database. One can go back and tweak the parameter once the BLAST is done.
        </p>
        <button id="blastBtn" class="btn btn-outline-primary mb-4" type="button">Go to NCBI Website</button>
      </div>
    </div>
    </div>
  </div>
  <div class="row my-2">
    <div class="col-sm-12">
      <h3 class="h3">16s rRNA sequence</h3>
      <hr />
    </div>
    <div class="col-sm-12">
      <a href="/api/v1/isolates/rrna/{{ $id }}">
        <button class="btn btn-outline-success" type="button">Download FASTA</button>
      </a>
      <div class="card card-body my-3" id="16sBox"></div>
    </div>
  </div>

  <!-- include functions necessary to build blast table -->
  <script src="/js/blastTable.js"></script>

  <script>
  function fetchGenome(id) {
    $.ajax({
      url: "/api/v1/isolates/relativeGenome/"+id, 
      success: function(data) {
        if (data.id == "") {
          let errorString = "<p>No related genome found in NCBI</p>";
          $("#genomeError").append(errorString);
          $("#genomeTable>.g-loading").remove();
          return;
        }
        for (let i = 0; i < data['id'].length; i++) {
          let tableRowString = `
            <tr>
              <th scope="row">${data['strain'][i]}</span></th>
              <td class="align-middle">
                <a class="badge badge-pill badge-success" href="https://www.ncbi.nlm.nih.gov/nuccore/${data['id'][i]}">NCBI page</a>
              </td>
              <td class="align-middle">
                <a class="badge badge-pill badge-primary" href="/api/v1/ncbi/genome/${data.id[i]}">Download</span>
              </td>
            </tr>`;
          $("#genomeTable>table>tbody").append(tableRowString);
        }

        // remove loading icon
        $("#genomeTable>.g-loading").remove();
      },
    
      error: function() {
        let errorString = '<p class="bg-danger">Unexpected server error encountered.</p>';
        $("#genomeError").append(errorString);
        $("#genomeTable>.g-loading").remove();
      }
    });
  };

  function goBlast(id) {
    $.ajax({
      url: "/api/v1/ncbi/blast/rid/" + id,
      success: function(data) {
        ncbiUrl = "https://blast.ncbi.nlm.nih.gov/Blast.cgi";
        $.redirect(ncbiUrl, data, 'POST', '_blank');
        $("#blastBtn").html("Go BLAST");
        $("#blastBtn").removeClass("disabled");
      },
      error: function() {
        let errorString = '<p class="bg-danger">Unexpected error: cannot submit NCBI BLAST request</P>';
        $("#genomeError").append(errorString);
        $("#blastBtn").html("Go BLAST");
        $("#blastBtn").removeClass("disabled");
      }
    });
  };

  $(document).ready(function() {
    // get id
    var id = '{{ $id }}';

    // get details
    $.ajax({
      url: '/api/v1/isolates/id/' + id,
      success: function(data) {
        $('#isoidInfo').html(data.isolate_id);
        $('#conditionInfo').html(data.condition);
        $('#orderInfo').html(data.order);
        $('#relativeInfo').html(data.closest_relative);
        $('#similarityInfo').html(data.similarity);
        $('#dateInfo').html(data.date_sampled);
        $('#sampleidInfo').html(data.sample_id);
        $('#labInfo').html(data.lab);
        $('#campaignInfo').html(data.campaign);
        if (data.fit_id) {
          $('#fitness-link').html(`
            <a href="http://fitprivate.genomics.lbl.gov/cgi-bin/org.cgi?orgId=${data.fit_id}">Link</a>
           `);
        }
        if (data.rrna) {
          $('#16sBox').html(`<p class="small">> ${data.isolate_id}<br />${data.rrna}</p>`);
        } else {
          $('#16sBox').html('<p class="bg-danger">16s rRNA sequence not found</p>');
        }
        // remove loading icon
        $('#loadingIcon').remove();
      }
    });

    // get genome list
    fetchGenome(id);

    // accomodate multiple buttons
    $('.g-button').click(function() {
      if(!$('#genomeCollapse').hasClass('show')) {
        $('#genomeCollapse').addClass('show');
      }
      $($(this).attr('data-target')).removeClass('d-none');
      $($(this).attr('hide-target')).addClass('d-none');
    });

    // implement local blast button
    $('#lbButton').one('click', function() {
      // createBlastObj() is defined in external js
      let lb = createBlastObj('lbTable', 'primary', 'isolates');
      lb.fetchBlast(id);
    });

    $('#silvaButton').one('click', function() {
      let silva = createBlastObj('silvaTable', 'warning', 'silva');
      silva.fetchBlast(id);
    });

    $('#ncbiButton').one('click', function() {
      let ncbib = createBlastObj('ncbiTable', 'success', 'ncbi');
      ncbib.fetchBlast(id);
    });

    // assign go blast button func
    $("#blastBtn").click(function() {
      $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Go BLAST');
      $(this).addClass('disabled');
      goBlast(id);
    });
  });
  </script>
@endsection
