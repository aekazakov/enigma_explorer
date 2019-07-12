@extends('template')

@section('title', 'Detail')

@section('content')
    <div class="sk-rotating-plane" id="loadingIcon"></div>
    <h1 class="h1 my-4" id="isoidInfo"></h1>
    <table class="table table-sm table-striped">
      <tbody>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Isolation Condition</span></h5></th>
          <td class="align-middle" id="conditionInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Order</span></h5></th>
          <td class="align-middle" id="orderInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Closest Relative in NCBI</span></h5></th>
          <td class="align-middle" id="relativeInfo"></td>
          <td><h5><span class="badge badge-pill badge-secondary">Similarity</span></h5></td>
          <td class="align-middle" id="similarityInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Date Sampled</span></h5></th>
          <td class="align-middle" id="dateInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Well/Sample ID</span></h5></th>
          <td class="align-middle" id="sampleidInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Lab Isolated</span></h5></th>
          <td class="align-middle" id="labInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h5><span class="badge badge-pill badge-secondary">Campaign or Set</span></h5></th>
          <td class="align-middle" id="campaignInfo"></td>
        </tr>
      </tbody>
    </table>
    <h3 class="h3">Relative genomes from NCBI</h3>
    <hr />
    <button id="genomeButton" class="btn btn-outline-success mb-2" type="button" data-toggle="collapse" data-target="#genomeCollapse">Download FASTA</button>
    <div id="genomeCollapse" class="collapse">
      <div class="card card-body">
        <div id="loadingIcon2" class="sk-wave">
          <div class="sk-rect sk-rect1"></div>
          <div class="sk-rect sk-rect2"></div>
          <div class="sk-rect sk-rect3"></div>
          <div class="sk-rect sk-rect4"></div>
          <div class="sk-rect sk-rect5"></div>
        </div>
        <table id="genomeTable" class="table table-sm">
          <tbody>
          </tbody>
        </table>
        <div id="genomeError"></div>
        <p class="small text-muted mt-4">Need more information?</p>
        <button id="blastBtn" class="btn btn-outline-primary mb-4" type="button">Go BLAST</button>
      </div>
    </div>
    <h3 class="h3 mt-2">16s rRNA sequence</h3>
    <hr />
    <a href="/api/v1/isolates/rrna/{{ $id }}">
      <button class="btn btn-outline-success mb-4" type="button">Download FASTA</button>
    </a>

    <script src="/js/jquery.redirect.js"></script>
    <script>
    function fetchGenome(id) {
      $.ajax({
        url: "/api/v1/isolates/relativeGenome/"+id, 
        success: function(data) {
          if (data.id == "") {
            let errorString = "<p>No related genome found in NCBI</p>";
            $("#genomeError").append(errorString);
            $("#loadingIcon2").remove();
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
            $("#genomeTable>tbody").append(tableRowString);
          }

          // remove loading icon
          $("#loadingIcon2").remove();
        },
      
        error: function() {
          let errorString = '<p class="bg-danger">Unexpected server error encountered.</p>';
          $("#genomeError").append(errorString);
          $("#loadingIcon2").remove();
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

          $('#loadingIcon').remove();
        }
      });

      // get genome list
      fetchGenome(id);

      // assign go blast button func
      $("#blastBtn").click(function() {
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Go BLAST');
        $(this).addClass('disabled');
        goBlast(id);
      });
    });
    </script>
@endsection
