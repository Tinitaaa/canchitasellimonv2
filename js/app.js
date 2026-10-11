/* ============================================================
   Canchitas El Limón - Turnos
   JavaScript del lado del cliente: menú, confirmaciones y
   validaciones. (Las validaciones importantes se repiten en PHP.)
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  // ---------- Menú en celulares ----------
  var botonMenu = document.getElementById('menuToggle');
  var menu = document.getElementById('mainNav');
  if (botonMenu && menu) {
    botonMenu.addEventListener('click', function () {
      var abierto = menu.classList.toggle('open');
      botonMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });
  }

  // ---------- Confirmación antes de enviar ciertos formularios ----------
  // Se activa con el atributo data-confirm="texto" en el <form>
  var formsConfirmar = document.querySelectorAll('form[data-confirm]');
  formsConfirmar.forEach(function (form) {
    form.addEventListener('submit', function (evento) {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        evento.preventDefault();
      }
    });
  });

  // ---------- Reservar: al cambiar la fecha se actualizan los horarios ----------
  var inputFecha = document.getElementById('fecha');
  if (inputFecha && inputFecha.form) {
    inputFecha.addEventListener('change', function () {
      if (inputFecha.value) {
        inputFecha.form.submit();
      }
    });
  }

  // ---------- Reservar: marcar el turno elegido y habilitar el botón ----------
  var formReserva = document.getElementById('formReserva');
  if (formReserva) {
    var botonConfirmar = document.getElementById('btnConfirmar');
    var radios = formReserva.querySelectorAll('input[name="horario"]');

    var actualizar = function () {
      var hayElegido = false;
      radios.forEach(function (radio) {
        var etiqueta = radio.closest('label');
        if (etiqueta) {
          etiqueta.classList.toggle('seleccionado', radio.checked);
        }
        if (radio.checked) {
          hayElegido = true;
        }
      });
      if (botonConfirmar) {
        botonConfirmar.disabled = !hayElegido;
      }
    };

    radios.forEach(function (radio) {
      radio.addEventListener('change', actualizar);
    });
    actualizar();
  }

  // ---------- Registro: validaciones antes de enviar ----------
  var formRegistro = document.getElementById('formRegistro');
  if (formRegistro) {
    var cajaError = document.getElementById('errorJS');

    formRegistro.addEventListener('submit', function (evento) {
      var errores = [];
      var campos = ['nombre', 'apellido', 'email', 'usuario', 'password', 'confirmacion'];

      var hayVacios = campos.some(function (id) {
        return document.getElementById(id).value.trim() === '';
      });
      if (hayVacios) {
        errores.push('Completá todos los campos.');
      }

      var email = document.getElementById('email').value.trim();
      if (email !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errores.push('El correo electrónico no es válido.');
      }

      var usuario = document.getElementById('usuario').value.trim();
      if (usuario !== '' && !/^[A-Za-z0-9_]{3,20}$/.test(usuario)) {
        errores.push('El usuario debe tener entre 3 y 20 caracteres (letras, números o guion bajo).');
      }

      var clave = document.getElementById('password').value;
      var confirmacion = document.getElementById('confirmacion').value;
      if (clave !== '' && clave.length < 8) {
        errores.push('La contraseña debe tener al menos 8 caracteres.');
      }
      if (clave !== confirmacion) {
        errores.push('Las contraseñas no coinciden.');
      }

      if (errores.length > 0) {
        evento.preventDefault();
        cajaError.textContent = errores.join(' ');
        cajaError.hidden = false;
        cajaError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        cajaError.hidden = true;
      }
    });
  }
});
