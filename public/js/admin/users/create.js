window.userForm = function() {
  return {
    password: '',
    passwordConfirmation: '',
    showPassword: false,
    showPasswordConfirmation: false,
    syncPasswordVisibility: false,
    passwordStrength: { show: false, width: 0, class: '', text: '', textClass: '' },
    passwordMatch: { show: false, class: '', icon: '', text: '' },

    init() {
      this.$watch('syncPasswordVisibility', (value) => { if (value) { this.syncPasswords(); } });
    },

    togglePassword(field) {
      if (field === 'password') {
        this.showPassword = !this.showPassword;
        const input = document.getElementById('password');
        input.type = this.showPassword ? 'text' : 'password';
        if (this.syncPasswordVisibility) {
          this.showPasswordConfirmation = this.showPassword;
          const confirmInput = document.getElementById('password_confirmation');
          confirmInput.type = this.showPassword ? 'text' : 'password';
        }
      } else if (field === 'password_confirmation') {
        this.showPasswordConfirmation = !this.showPasswordConfirmation;
        const input = document.getElementById('password_confirmation');
        input.type = this.showPasswordConfirmation ? 'text' : 'password';
        if (this.syncPasswordVisibility) {
          this.showPassword = this.showPasswordConfirmation;
          const passwordInput = document.getElementById('password');
          passwordInput.type = this.showPasswordConfirmation ? 'text' : 'password';
        }
      }
    },

    syncPasswords() {
      this.showPasswordConfirmation = this.showPassword;
      const confirmInput = document.getElementById('password_confirmation');
      confirmInput.type = this.showPassword ? 'text' : 'password';
    },

    checkPasswordStrength() {
      if (!this.password) { this.passwordStrength.show = false; return; }
      let strength = 0;
      if (this.password.length >= 8) strength += 1;
      if (/[a-z]/.test(this.password)) strength += 1;
      if (/[A-Z]/.test(this.password)) strength += 1;
      if (/[0-9]/.test(this.password)) strength += 1;
      if (/[^a-zA-Z0-9]/.test(this.password)) strength += 1;
      this.passwordStrength.show = true;
      if (strength < 2) {
        this.passwordStrength.width = 25; this.passwordStrength.class = 'weak';
        this.passwordStrength.text = 'Contraseña débil'; this.passwordStrength.textClass = 'weak';
      } else if (strength < 4) {
        this.passwordStrength.width = 50; this.passwordStrength.class = 'medium';
        this.passwordStrength.text = 'Contraseña media'; this.passwordStrength.textClass = 'medium';
      } else {
        this.passwordStrength.width = 100; this.passwordStrength.class = 'strong';
        this.passwordStrength.text = 'Contraseña fuerte'; this.passwordStrength.textClass = 'strong';
      }
    },

    checkPasswordMatch() {
      if (!this.passwordConfirmation) { this.passwordMatch.show = false; return; }
      this.passwordMatch.show = true;
      if (this.password === this.passwordConfirmation) {
        this.passwordMatch.class = 'match'; this.passwordMatch.icon = 'fa-check-circle';
        this.passwordMatch.text = 'Las contraseñas coinciden';
      } else {
        this.passwordMatch.class = 'no-match'; this.passwordMatch.icon = 'fa-times-circle';
        this.passwordMatch.text = 'Las contraseñas no coinciden';
      }
    },

    submitForm() {
      if (this.password !== this.passwordConfirmation) { this.showAlert('Error de validación', 'Las contraseñas no coinciden', 'error'); return false; }
      if (this.password && this.passwordStrength.width < 50) { this.showAlert('Contraseña débil', 'Por favor, use una contraseña más segura', 'warning'); return false; }
      const submitBtn = this.$refs.submitBtn;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
      submitBtn.disabled = true;
      return true;
    },

    showAlert(title, text, icon) {
      if (typeof Swal !== 'undefined') { Swal.fire({ icon, title, text, confirmButtonText: 'Entendido' }); }
      else { alert(`${title}: ${text}`); }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  
  // Asegurar que Alpine.js esté listo antes de mostrar el contenido
  if (typeof Alpine !== 'undefined') {
    Alpine.nextTick(() => {
      // Remover x-cloak después de que Alpine.js se haya inicializado
      const form = document.querySelector('form[x-cloak]');
      if (form) {
        form.removeAttribute('x-cloak');
      }
    });
  }
});
