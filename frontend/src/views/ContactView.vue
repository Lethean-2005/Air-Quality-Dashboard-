<template>
  <!-- Floating Chat Icon - Smaller -->
  <div class="floating-chat-icon" @click="toggleModal">
    <div class="chat-icon" :class="{ 'active': isOpen }">
      <svg class="chat-ring" viewBox="0 0 100 100" width="92" height="92" aria-hidden="true">
        <defs>
          <path id="chatRingPath" d="M 50,50 m -38,0 a 38,38 0 1,1 76,0 a 38,38 0 1,1 -76,0" />
        </defs>
        <text font-size="9" font-weight="700" fill="currentColor" letter-spacing="2">
          <textPath href="#chatRingPath" startOffset="0%">I AM HERE &#8226; I AM HERE &#8226; </textPath>
        </text>
      </svg>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
        <path d="M9 10a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
        <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
      </svg>
    </div>
  </div>

  <!-- Contact Form Popup -->
  <div v-if="isOpen" class="contact-form-popup" :class="{ 'slide-in': isOpen }">
    <!-- Header -->
    <div class="form-header">
      <button class="close-btn" @click="closeModal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
      <div class="header-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
          <path d="M9 10a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
          <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
        </svg>
      </div>
    </div>

    <!-- Content -->
    <div class="form-content">
      <h2 class="form-title">Contact Us</h2>
      <p class="form-subtitle">
        Do you need any air quality monitor, sensor,<br>
        API data or AQI solutions?
      </p>

      <form @submit.prevent="handleSubmit" class="contact-form">
        <!-- Full Name -->
        <div class="input-group">
          <input
            v-model="formData.fullName"
            type="text"
            placeholder="Full Name"
            class="form-input"
            required
          />
          <span class="required-mark">*</span>
        </div>

        <!-- Email -->
        <div class="input-group">
          <input
            v-model="formData.email"
            type="email"
            placeholder="Email"
            class="form-input"
            required
          />
          <span class="required-mark">*</span>
        </div>

        <!-- Phone Number -->
        <div class="input-group">
          <input
            v-model="formData.phoneNumber"
            type="tel"
            placeholder="Phone Number"
            class="form-input"
            required
          />
          <span class="required-mark">*</span>
        </div>

        <!-- Purpose of Contact -->
        <div class="input-group">
          <div class="select-wrapper">
            <button
              type="button"
              class="select-button"
              @click="showDropdown = !showDropdown"
            >
              {{ formData.purposeOfContact || 'Select a topic' }}
              <svg
                class="select-arrow"
                :class="{ 'rotated': showDropdown }"
                width="12"
                height="12"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <polyline points="6,9 12,15 18,9"></polyline>
              </svg>
            </button>
            <div v-if="showDropdown" class="select-dropdown">
              <div
                v-for="option in purposeOptions"
                :key="option"
                class="select-option"
                @click="selectPurpose(option)"
              >
                {{ option }}
              </div>
            </div>
          </div>
        </div>

        <!-- Organisation -->
        <div class="input-group">
          <input
            v-model="formData.organisation"
            type="text"
            placeholder="Organisation"
            class="form-input"
          />
        </div>

        <!-- Message -->
        <div class="input-group">
          <textarea
            v-model="formData.message"
            placeholder="Write your message here"
            class="form-textarea"
            rows="3"
            required
          ></textarea>
          <span class="required-mark">*</span>
          <span class="resize-handle" aria-hidden="true"></span>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="submit-button"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Sending...' : 'Send Message' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { API_ROOT } from '@/services/api.js';

export default {
  name: 'ContactFormModal',
  data() {
    return {
      isOpen: false,
      formData: {
        fullName: '',
        email: '',
        phoneNumber: '',
        purposeOfContact: '',
        organisation: '',
        message: ''
      },
      showDropdown: false,
      isSubmitting: false,
      purposeOptions: [
        'Air Quality Monitor',
        'Sensor Solutions',
        'API Data',
        'AQI Solutions',
        'General Inquiry',
        'Technical Support',
        'Partnership',
        'Other'
      ]
    }
  },
  methods: {
    toggleModal() {
      this.isOpen = !this.isOpen
    },
    closeModal() {
      this.isOpen = false
      this.showDropdown = false
    },
    selectPurpose(option) {
      this.formData.purposeOfContact = option
      this.showDropdown = false
    },
    async handleSubmit() {
      if (
        !this.formData.fullName ||
        !this.formData.email ||
        !this.formData.phoneNumber ||
        !this.formData.message
      ) {
        alert('Please fill in all required fields')
        return
      }

      this.isSubmitting = true

      try {
        const response = await axios.post(`${API_ROOT}/api/contact`, {
          full_name: this.formData.fullName,
          email: this.formData.email,
          phone_number: this.formData.phoneNumber,
          purpose_of_contact: this.formData.purposeOfContact,
          organisation: this.formData.organisation,
          message: this.formData.message,
        })

        // Successful submission feedback
        alert('Message sent successfully!')
        this.closeModal()
        this.resetForm()
      } catch (error) {
        console.error('Submit error:', error)
        // Show error message from Laravel or generic fallback
        alert(
          error.response?.data?.message || 'Failed to send message. Please try again.'
        )
      } finally {
        this.isSubmitting = false
      }
    },
    resetForm() {
      this.formData = {
        fullName: '',
        email: '',
        phoneNumber: '',
        purposeOfContact: '',
        organisation: '',
        message: ''
      }
      this.showDropdown = false
    }
  }
}
</script>

<style scoped>
/* Your existing styles here (keep as-is) */
.floating-chat-icon {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1001;
  cursor: pointer;
}
.chat-icon {
  position: relative;
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 16px rgba(79, 70, 229, 0.3);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid rgba(255, 255, 255, 0.1);
}
.chat-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  color: rgba(79, 70, 229, 0.85);
  pointer-events: none;
  animation: chat-ring-spin 9s linear infinite;
}
@keyframes chat-ring-spin {
  from { transform: translate(-50%, -50%) rotate(0deg); }
  to { transform: translate(-50%, -50%) rotate(360deg); }
}
.chat-icon:hover {
  transform: scale(1.05) translateY(-2px);
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
  background: linear-gradient(135deg, #5b21b6 0%, #4f46e5 100%);
}
.chat-icon.active {
  background: linear-gradient(135deg, #5b21b6 0%, #4f46e5 100%);
  transform: scale(0.95);
  box-shadow: 0 2px 8px rgba(79, 70, 229, 0.4);
}
.contact-form-popup {
  position: fixed;
  bottom: 80px;
  right: 20px;
  width: 340px;
  background: linear-gradient(160deg, #46586b 0%, #2b3646 55%, #1c2531 100%);
  border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.06);
  z-index: 1000;
  transform: translateX(100%) scale(0.8);
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: visible;
}
.contact-form-popup.slide-in {
  transform: translateX(0) scale(1);
  opacity: 1;
}
.form-header {
  position: relative;
  padding: 22px 16px 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.close-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  background: transparent;
  border: none;
  color: #f1f5f9;
  cursor: pointer;
  padding: 2px;
  border-radius: 4px;
  transition: opacity 0.2s ease;
  opacity: 0.85;
}
.close-btn:hover {
  opacity: 1;
}
.header-icon {
  width: 46px;
  height: 46px;
  background: #0f1520;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f1f5f9;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}
.form-content {
  padding: 0 16px 16px;
}
.form-title {
  color: white;
  font-size: 20px;
  font-weight: 700;
  text-align: center;
  margin: 8px 0 4px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
.form-subtitle {
  color: #cbd5e0;
  text-align: center;
  margin-bottom: 16px;
  line-height: 1.4;
  font-size: 13px;
  opacity: 0.9;
}
.contact-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.input-group {
  position: relative;
}
.input-label {
  color: #e2e8f0;
  font-size: 12px;
  margin-bottom: 4px;
  display: block;
  font-weight: 500;
}
.form-input,
.form-textarea {
  width: 100%;
  background: rgba(255, 255, 255, 0.07);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 8px;
  padding: 10px 12px;
  color: white;
  font-size: 13px;
  transition: all 0.3s ease;
  box-sizing: border-box;
}
.form-input::placeholder,
.form-textarea::placeholder {
  color: #cbd5e1;
}
.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b9eea;
  box-shadow: 0 0 0 2px rgba(59, 158, 234, 0.25);
  background: rgba(255, 255, 255, 0.1);
}
.form-textarea {
  resize: vertical;
  min-height: 64px;
}
.resize-handle {
  position: absolute;
  bottom: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  pointer-events: none;
  background: #8b5cf6;
  clip-path: polygon(100% 0, 0 100%, 100% 100%);
  border-radius: 1px;
}
.required-mark {
  position: absolute;
  top: 10px;
  right: 10px;
  color: #ef4444;
  font-weight: bold;
  font-size: 12px;
}
.select-wrapper {
  position: relative;
}
.select-button {
  width: 100%;
  background: #10141c;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 10px 12px;
  color: white;
  font-weight: 600;
  font-size: 13px;
  text-align: left;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.3s ease;
}
.select-button:hover {
  border-color: rgba(255, 255, 255, 0.25);
  background: #171c26;
}
.select-arrow {
  transition: transform 0.2s ease;
}
.select-arrow.rotated {
  transform: rotate(180deg);
}
.select-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #171c26;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  margin-top: 2px;
  z-index: 10;
  max-height: 120px;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}
.select-option {
  padding: 8px 12px;
  color: white;
  cursor: pointer;
  transition: background 0.2s ease;
  font-size: 13px;
}
.select-option:hover {
  background: rgba(59, 158, 234, 0.2);
}
.submit-button {
  background: #4dabf7;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
  margin-top: 6px;
}
.submit-button:hover:not(:disabled) {
  background: #3b9eea;
}
.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}
/* Mobile Responsiveness */
@media (max-width: 640px) {
  .contact-form-popup {
    width: 280px;
    right: 10px;
    bottom: 70px;
  }
  
  .floating-chat-icon {
    bottom: 15px;
    right: 15px;
  }
  
  .chat-icon {
    width: 45px;
    height: 45px;
  }
}
</style>
