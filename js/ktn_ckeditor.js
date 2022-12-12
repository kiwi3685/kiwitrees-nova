
ClassicEditor
	.create( document.querySelector(".html-edit"), {
		licenseKey: "",
	})
	.then( editor => {
		window.editor = editor;
	});