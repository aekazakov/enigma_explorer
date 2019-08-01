<div id="{{ $id }}" class="d-none">
  <!-- A blastTable is defined to be a component the contain 3 divs -->
  <!-- The loading, the table, and the error div -->
  <div class="g-hint m-4">
    {{ $slot }}
  </div>
  <div class="g-loading sk-wave">
    <div class="sk-rect sk-rect1"></div>
    <div class="sk-rect sk-rect2"></div>
    <div class="sk-rect sk-rect3"></div>
    <div class="sk-rect sk-rect4"></div>
    <div class="sk-rect sk-rect5"></div>
  </div>
  <table class="table table-sm">
    <tbody></tbody>
  </table>
  <div class="g-error"></div>
</div>
