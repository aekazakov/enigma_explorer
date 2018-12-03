@extends('template')

@section('title', 'Detail')

@section('content')
    <div class="sk-rotating-plane" id="loadingIcon"></div>
    <h1 class="h1 my-4" id="isoidInfo"></h1>
    <table class="table">
      <tbody>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Isolation Condition</span></h4></th>
          <td class="align-middle" id="conditionInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Order</span></h4></th>
          <td class="align-middle" id="orderInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Closest Relative in NCBI</span></h4></th>
          <td class="align-middle" id="relativeInfo"></td>
          <td><h4><span class="badge badge-pill badge-secondary">Similarity</span></h4></td>
          <td class="align-middle" id="similarityInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Date Sampled</span></h4></th>
          <td class="align-middle" id="dateInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Well/Sample ID</span></h4></th>
          <td class="align-middle" id="sampleidInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Lab Isolated</span></h4></th>
          <td class="align-middle" id="labInfo"></td>
        </tr>
        <tr>
          <th scope="row"><h4><span class="badge badge-pill badge-secondary">Campaign or Set</span></h4></th>
          <td class="align-middle" id="campaignInfo"></td>
        </tr>
      </tbody>
    </table>
    <h2 class="h2">16s rRNA sequence</h2>
    <hr />
    <a href="/api/v1/isolates/rrna/{{ $id }}">
      <button class="btn btn-outline-success mb-5" type="button">Download FASTA</button>
    </a>
    <h2 class="h2 my-2">Genome of closest relative from NCBI</h2>
    <hr />
    <button class="btn btn-outline-success mb-5" type="button">Download FASTA</button>

    <script>
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
    });
    </script>
@endsection
