@extends('template')

@section('title', 'Test')

@section('content')
  <a id="blastBtn" class="btn btn-lg btn-outline-primary" href="javascript:void(0)">
    BLAST ME!
  </a>
  <a id="ridBtn" class="btn btn-lg btn-outline-primary" href="javascript:void(0)">
    SEE REPORT HERE!
  </a>
</body>
<div class="spinner-grow text-primary" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-secondary" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-success" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-danger" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-warning" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-info" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-light" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-dark" role="status">
  <span class="sr-only">Loading...</span>
</div>
  <script src="/js/jquery.redirect.js"></script>
  <script>
  $(document).ready(function() {
    $("#blastBtn").click(function() {
      let ncbiUrl = "https://blast.ncbi.nlm.nih.gov/Blast.cgi";
      let rRNA = ">FW305-130\nGCAGTCGAGCGGTAAGGCCTTTCGGGGTACACGAGCGGCGAACGGGTGAGTAACACGTGGGTGATCTGCCCTGCACTTCGGGATAAGCCTGGGAAACTGGGTCTAATACCGGATATGACCTCAGGTTGCATGACTTGGGGTGGAAAGATTTATCGGTGCAGGATGGGCCCGCGGCCTATCAGCTTGTTGGTGGGGTAATGGCCTACCAAGGCGACGACGGGTAGCCGACCTGAGAGGGTGACCGGCCACACTGGGACTGAGACACGGCCCAGACTCCTACGGGAGGCAGCAGTGGGGAATATTGCACAATGGGCGAAAGCCTGATGCAGCGACGCCGCGTGAGGGATGACGGCCTTCGGGTTGTAAACCTCTTTCAGCAGGGACGAAGCGCAAGTGACGGTACCTGCAGAAGAAGCACCGGCTAACTACGTGCCAGCAGCCGCGGTAATACGTAGGGTGCAAGCGTTGTCCGGAATTACTGGGCGTAAAGAGTTCGTAGGCGGTTTGTCGCGTCGTTTGTGAAAACCAGCAGCTCAACTGCTGGCTTGCAGGCGATACGGGCAGACTTGAGTACTGCAGGGGAGACTGGAATTCCTGGTGTAGCGGTGAAATGCGCAGATATCAGGAGGAACACCGGTGGCGAAGGCGGGTCTCTGGGCAGTAACTGACGCTGAGGAACGAAAGCGTGGGTAGCGAACAGGATTAGATACCCTGGTAGTCCACGCCGTAAACGGTGGGCGCTAGGTGTGGGTTCCTTCCACGGAATCCGTGCCGTAGCTAACGCATTAAGCGCCCCGCCTGGGGAGTACGGCCGCAAGGCTAAAACTCAAAGGAATTGACGGGGGCCCGCACAAGCGGCGGAGCATGTGGATTAATTCGATGCAACGCGAAGAACCTTACCTGGGGTTTGACATATACCGGAAAGCTGCAGAGATGTGGCCCCCCTTGTGGTCGGTATACAGGTGGTGCATGGCTGTCGTCAGCTCGTGTCGTGAGATGTTGGGTTAAGTCCCGCAACGAGCGCAACCCCTATCTTATGTTGCCAGCACGTTATGGTGGGGACTCGTAAGAGACTGCCGGGGTCAACTCGGAGGAAGGTGGGGACGACGTCAAGTCATCATGCCCCTTATGTCCAGGGCTTCACACATGCTACAATGGCCAGTACAGAGGGCTGCGAGACCGTGAGGTGGAGCGAATCCCTTAAAGCTGGTCTCAGTTCGGATCGGGGTCTGCAACTCGACCCCGTGAAGTNGGAGTCGCTAGTAATCGCAGATCAGCAACGCTGCGGTGAATACGTTCCCGGGCCTTGTACACACCGCCCGTCACGTCATGAAAGTCGGTAACACCCGAAGCCGGTGGCT";
      let postData = {
        "CMD": "Put",
        "PROGRAM": "blastn",
        "MEGABLAST": "on",
        "DATABASE": "nr",
        "QUERY": rRNA
      };
      $.redirect(ncbiUrl, postData, "POST", "_blank");
    });

    $("#ridBtn").click(function() {
      let ncbiUrl = "https://blast.ncbi.nlm.nih.gov/Blast.cgi";
      let postData = {
        "CMD": "Get",
        "FORMAT_TYPE": "HTML",
        "RID": "23GWEB0R015",
        "SHOW_OVERVIEW": "on"
      };
      $.redirect(ncbiUrl, postData, "POST", "_blank");
    });
  });
  </script>
@endsection
