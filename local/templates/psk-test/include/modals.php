<div class="modal fade" id="callback" tabindex="-1" aria-labelledby="callback" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Узнать цену</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-callback" class="form">
          <input type="hidden" name="id">
          <div class="form__field">
            <input class="form__input" require name="name" type="text" placeholder="Имя">
          </div>
          <div class="form__field">
            <input class="form__input" require name="phone" type="text" placeholder="Телефон">
          </div>
          <div class="form__field">
            <div class="error">
              Поля не могут быть пустыми!
            </div>
          </div>
          <div class="form__field">
            <button type="submit" class="btn modal__button">Отправить</button>
          </div>
        </form>

        <script>
          document.addEventListener('DOMContentLoaded', () => {
            const modal = document.querySelector('#callback')
            const form = modal.querySelector('#form-callback');
            const body = modal.querySelector('.modal-body');
            const inputName = modal.querySelector('[name="name"]');
            const inputPhone = modal.querySelector('[name="phone"]');

            form.addEventListener('submit', (e) => {
              e.preventDefault();

              if (inputPhone.value.length > 0 && inputName.value.length > 0) {
                const response = fetch('/ajax/mail.php', {
                  method: 'POST',
                  body: new FormData(form)
                })
                response.then(res => res.json())
                  .then(res => {

                    if (res.status === 200) {
                      const template = `<div class="message">${res.text}</div>`
                      body.innerHTML = template;

                    } else {
                      const template = `<div class="message">Ошибка отправки сообщения</div>`
                      body.innerHTML = template;
                    }

                  })
              } else {
                const error = form.querySelector('.error');
                error.classList.add('active');
              }

            })
          })
        </script>
      </div>
    </div>
  </div>
</div>