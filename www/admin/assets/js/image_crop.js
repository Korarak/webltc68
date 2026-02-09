let cropper;

function initImageCrop(inputId, modalId, previewId, hiddenInputId, aspectRatio = 16/9, outputWidth = 1280, outputHeight = 720) {
  const uploadInput = document.getElementById(inputId);
  const cropModal = document.getElementById(modalId);
  const imagePreview = document.getElementById(previewId);
  const hiddenInput = document.getElementById(hiddenInputId);

  uploadInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      imagePreview.src = reader.result;
      cropModal.classList.remove('hidden');
      cropper = new Cropper(imagePreview, {
        aspectRatio: aspectRatio,
        viewMode: 1,
        autoCropArea: 1
      });
    };
    reader.readAsDataURL(file);
  });

  document.getElementById(modalId + "_cancel").addEventListener('click', () => {
    cropper.destroy();
    cropModal.classList.add('hidden');
    uploadInput.value = '';
  });

  document.getElementById(modalId + "_confirm").addEventListener('click', () => {
    const canvas = cropper.getCroppedCanvas({
      width: outputWidth,
      height: outputHeight
    });
    hiddenInput.value = canvas.toDataURL("image/jpeg");
    cropper.destroy();
    cropModal.classList.add('hidden');
  });
}
