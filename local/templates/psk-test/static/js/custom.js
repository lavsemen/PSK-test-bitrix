function getApartmentId () {
  const buttonTrigger = document.querySelector('#callbackModal');

  if (buttonTrigger) {
    const form = document.querySelector('#form-callback');
    const idField = form.querySelector('[name="id"]')
    buttonTrigger.addEventListener('click', (event) => {
      const id = event.target.dataset.apartmentId
      idField.value = id;
    })
  }

}


document.addEventListener('DOMContentLoaded', () => {
  getApartmentId();
})