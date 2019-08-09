@extends('template')

@section('title', 'Growth Curve')

@section('content')
  @component('searchBox')
    Search for a Plate
  @endcomponent
  
  <script>
    $(document).ready(function() {
      $('#mainSearchButton').unbind();
      $('#mainSearchButton').click(function() {
        let id = $('#mainSearchInput').val();
        window.location.href = '/growthcurve/id/'+id;
      });
    });
  </script>
@endsection
