@extends('template')

@section('title', 'Isolates')

@section('content')
    @component('searchBox')
      Search for isolates
    @endcomponent
    <div class="row my-4">
      <div class="col"> 
        <h1>Isolates</h1>
      </div>
    </div>
    <div class="row my-4" style="font-size:1.2em;">
      <div class="col">
        <p>Search for isolates by isolates ID, pylogenic orders or closest relatives. Can't remember Latin? The search box accepts partial word and would give hints.</p>
        <p>More accurate search? Hit the <em>Advance Search</em> button to start!</p>
        <p>For questions and suggestions, please contact <a href="mailto:lmlui@lbl.gov">Lauren Lui</a></p>
      </div>
    </div>
    <div class="row">
      <div class="col-12 my-2">
        <h3>BLAST against local ENIGMA sequences</h3>
        <hr />
      </div>
      <div class="col-12">
        <form>
          <div class="form-group row my-2">
            <label for="dbSelect" class="col-md-3">Choose BLAST database</label>
            <div class="col-md-9">
              <select id="dbSelect" class="custom-select">
                <option value="ncbi">NCBI Bacteria and Archaea 16s rRNA</option>
                <option value="silva">SILVA SSU</option>
                <option selected value="isolates">ENIGMA isolates 16s rRNA</option>
              </select>
            </div>
            <div class="col-md-12 my-2">
              <textarea id="seqText" class="form-control form-control-sm col-12" rows="10"></textarea>
            </div>
          </div>
          <button id="submitBtn" type="button" role="button" class="btn btn-outline-primary my-2">Submit</button>
        </form>
      </div>
    </div>
    <div class="row collapse" id="dataView">
      <div class="col-12 my-4">
        <div class="card card-body">
          @component('blastTable', [ 'id' => 'lbTable' ])
          @endcomponent
        </div>
      </div>
    </div>

  <script src="/js/blastTable.js"></script>
  <script>
    $(document).ready(function() {
      $('#submitBtn').on('click', function() {
        // remove everything already in the table
        $('#dataView tbody').html('');
        $('#dataView thead').remove();
        $('#dataView .g-error').html('');
        // show dataView if not already
        $('#dataView').addClass('show');
        // if there was a request, loading div will be hidden
        $('#dataView .g-loading').removeClass('d-none');

        // Construct request body
        let rBody = { 'seq': $('#seqText').val() };
        // empty blast table
        $('#lbTable tbody').html("");
        // fetch new info
        $('#lbTable').removeClass('d-none');
        let lb = createBlastObj('lbTable', 'success', $('#dbSelect').val());
        lb.fetchBlast(rBody);
      });
    });
  </script>
@endsection
