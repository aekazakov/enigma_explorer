@extends('template')

@section('title', 'Growth Curve')

@section('content')
  @component('searchBox')
    Search for a Plate
  @endcomponent
  
  <script>
    $(document).ready(function() {
      var newRedirect = () => {
        let keyword = encodeURI($('#mainSearchInput').prop('value'));
        // if a number, jump directly
        if (!isNaN(parseInt(keyword))) {
          window.location.href = '/growthcurve/id/'+keyword;
        } else {
          window.location.href = '/growthsearch?keyword='+keyword;
        }
      };

      setInterval(() => {
        $('#mainSearchButton').unbind();
        $('#mainSearchInput').unbind();

        $('#mainSearchButton').click(newRedirect);
        $('#mainSearchInput').bind('keypress', function(event) {
          if (event.keyCode == '13') {
            newRedirect();
          }
        });
      }, 100);

    });
  </script>
@endsection
