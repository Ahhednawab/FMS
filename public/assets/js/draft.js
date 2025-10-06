(function(){
  window.removeDraftFile = function(field, btn){
    try{
      if(!confirm('Remove attached file?')) return;
      var form = btn.closest('form');
      if(!form) return;
      var draftIdInput = form.querySelector('input[name="draft_id"]');
      if(!draftIdInput){ return; }
      var draftId = draftIdInput.value;

      var token = form.querySelector('input[name="_token"]').value;

      fetch(form.getAttribute('action').replace(/\/vehicles.*/, '') + '/drafts/' + draftId + '/remove-file', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ field: field })
      }).then(r => r.json()).then(res => {
        if(res && res.field === field){
          var previewRow = btn.closest('.d-flex');
          if(previewRow){ previewRow.remove(); }
        }
      }).catch(function(){});
    }catch(e){}
  };
})();


