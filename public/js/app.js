$(function(){
  $('#confirmationModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var cid = button.data('calculation-id');
    var modal = $(this);
    modal.find('.modal-footer form').attr('action', urlTemplate.replace('%id%', cid));
  })
});
