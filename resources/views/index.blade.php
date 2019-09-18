@extends('template')

@section('title', 'Main')

@section('content')
    <img class="img-fluid" src="/pics/isolates-banner.jpg" />
    <div class="row my-5">
      <div class="col">
        <h1 class="diaplay-1 text-white">ENIGMA</h1>
      </div>
    </div>
    <div class="row my-4">
      <div class="col text-white" style="font-size:1.2em;"> 
        <p>An ENIGMA resource for commonly used synthetic communities, enrichments, and isolates.</p>
        <p>For questions or suggestions, please contact <a href="mailto:lmlui@lbl.gov">Lauren Lui</a></p>
        <p>For technical support, please contact <a href="mailto:rainl199922@gmail.berkeley.edu">Yujia Liu</a></p>
      </div>
    </div>
    <h2 class="my-2 text-white">Resources</h2>
    <hr />
    <div class="row my-2">
      <div class="col-sm-12 col-lg-4">
        <a href="http://mprice.dev.microbesonline.org/curves/" class="btn btn-outline-info btn-lg d-block mx-auto my-2">ENIGMA Isolates Interaction</a>
      </div>
      <div class="col-sm-12 col-lg-4">
        <a href="https://narrative.kbase.us" class="btn btn-outline-info btn-lg d-block mx-auto my-2">KBase Narrative</a>
      </div>
      <div class="col-sm-12 col-lg-4">
        <a href="/isolates" class="btn btn-outline-info btn-lg d-block mx-auto my-2">Search for Isolates</a>
      </div>

    <script>
      $(document).ready(function() {
        //$('.container').addClass('container-fluid');
        //$('.container-fluid').removeClass('container');
        $('body').css('background-color', '#020305');
      });
    </script>
@endsection
