document.addEventListener('DOMContentLoaded', function() {
    // =====================================================
    // Mobile Sidebar Toggle
    // =====================================================
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.getElementById('sidebar');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    // Close sidebar when clicking a link on mobile
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                }
            });
        });
    }

    // =====================================================
    // TTS (Text-to-Speech) for Survey Introduction
    // =====================================================
    const ttsBtn = document.getElementById('tts-btn');
    if (ttsBtn) {
        const introText = ttsBtn.dataset.text;
        let speechActive = false;
        let utterance = null;

        ttsBtn.addEventListener('click', function() {
            if (!speechActive) {
                window.speechSynthesis.cancel();
                utterance = new SpeechSynthesisUtterance(introText);
                utterance.lang = 'en-US';
                utterance.rate = 1.0;
                utterance.pitch = 1.0;

                utterance.onend = function() {
                    ttsBtn.textContent = "\uD83D\uDD0A Listen to Introduction (Read Aloud)";
                    ttsBtn.classList.remove('btn-danger');
                    ttsBtn.classList.add('btn-primary');
                    speechActive = false;
                };

                utterance.onerror = function() {
                    ttsBtn.textContent = "\uD83D\uDD0A Listen to Introduction (Read Aloud)";
                    ttsBtn.classList.remove('btn-danger');
                    ttsBtn.classList.add('btn-primary');
                    speechActive = false;
                };

                window.speechSynthesis.speak(utterance);
                ttsBtn.textContent = "\u23F9\uFE0F Stop Reading Aloud";
                ttsBtn.classList.remove('btn-primary');
                ttsBtn.classList.add('btn-danger');
                speechActive = true;
            } else {
                window.speechSynthesis.cancel();
                ttsBtn.textContent = "\uD83D\uDD0A Listen to Introduction (Read Aloud)";
                ttsBtn.classList.remove('btn-danger');
                ttsBtn.classList.add('btn-primary');
                speechActive = false;
            }
        });
    }

    // =====================================================
    // Smooth Scroll for Anchor Links
    // =====================================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // =====================================================
    // Form Validation Visual Feedback
    // =====================================================
    const surveyForm = document.getElementById('survey-form');
    if (surveyForm) {
        surveyForm.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = surveyForm.querySelectorAll('[required]');

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#C62828';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Show a temporary alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-error';
                alertDiv.textContent = 'Please fill in all required fields before submitting.';
                surveyForm.insertBefore(alertDiv, surveyForm.firstChild);

                setTimeout(function() {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 4000);
            }
        });

        // Clear error styling on input
        surveyForm.querySelectorAll('[required]').forEach(function(field) {
            field.addEventListener('input', function() {
                this.style.borderColor = '#ddd';
            });
        });
    }

    // =====================================================
    // Close Mobile Sidebar on Outside Click
    // =====================================================
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && e.target !== mobileToggle) {
                sidebar.classList.remove('open');
            }
        }
    });

    // =====================================================
    // Print Styles Helper (add class for print optimization)
    // =====================================================
    window.addEventListener('beforeprint', function() {
        if (sidebar) {
            sidebar.style.display = 'none';
        }
        document.querySelector('.main-content').style.marginLeft = '0';
    });

    window.addEventListener('afterprint', function() {
        if (sidebar) {
            sidebar.style.display = '';
        }
        if (window.innerWidth > 768) {
            document.querySelector('.main-content').style.marginLeft = '260px';
        }
    });
});
