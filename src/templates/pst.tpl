
<div id="pst">
  <a href="#" id="pst-delete" class="pst_btn" data-target="pst-ds" data-action="fadeIn">Delete</a>
  <a href="#" id="pst-move" class="pst_btn" data-target="pst-ms" data-action="fadeIn">Move</a>
  <a href="/admin/pages/<{var:id}>" id="pst-edit" class="pst_btn" data-target="pst-es" data-action="fadeIn">Edit</a>
</div>
<div id="saved">saved</div>
<div id="error">error</div>


<input type="hidden" id="pid" value="<{var:id}>">

<form id="pst-es" class="container screen">
  <div id="pst-es-top">
    <input id="pst-es-title" name="title" placeholder="Page Title" value="<{var:title}>">
    <div id="pst-es-actions">
      <select id="pst-es-type"><{array:types:<option value="@type" @checked>@type</option>}></select>
      <a id="pst-es-save" href="#" class="pst_btn" data-target="pst-es" data-action="submit">Save</a>
      <a id="pst-es-done" href="#" class="pst_btn" data-target="pst-es" data-action="fadeOut">Done</a>
    </div>
  </div>
  
  <div id="pst-es-fields"><{var:fields}></div>
</form>

<form id="pst-ds" class="container screen">
  <h1>Are you sure?</h1>
  <p>You're about to permanently delete this page.  It cannot be recovered.</p>
  <a id="pst-ds-y" href="#" class="pst_btn txt" data-target="pst-ds" data-action="submit">Yes</a>
  <a id="pst-ds-n" href="#" class="pst_btn txt" data-target="pst-ds" data-action="fadeOut">No</a>
</form>

<form id="pst-ms" class="container screen">
	<h1>Move Page</h1>
	<p>You may change the URI of the page with this form.</p>
		<div class="container">
			<div class="form_icfix c aln-l" style="width:60%; display: inline-block;">
				<div>URI:</div>
				<input id="puri" name="uri" class="form_input form_field" placeholder="Page URI" value="<{var:uri}>" autocomplete="off">     
			</div>
		</div>
    <a id="pst-ms-s" href="#" class="pst_btn txt" data-target="pst-ms" data-action="submit">Submit</a>
    <a id="pst-ms-c" href="#" class="pst_btn txt" data-target="pst-ms" data-action="fadeOut">Cancel</a>
</form>
<script> </script><!-- LEAVE THIS HERE (see https://bugs.chromium.org/p/chromium/issues/detail?id=332189) -->