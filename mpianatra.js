// ===== SCRIPT HO AN'NY MPIANATRA =====

document.addEventListener('DOMContentLoaded', function() {
    // Fitantanana ny fampisehoana hafatra
    const hafatra = document.querySelector('.hafatra');
    if (hafatra) {
        setTimeout(() => {
            hafatra.style.opacity = '0';
            setTimeout(() => {
                hafatra.remove();
            }, 300);
        }, 5000);
    }

    // Fitantanana ny daty ankehitriny
    const datyInput = document.getElementById('daty');
    if (datyInput) {
        const ankehitriny = new Date();
        const datyAnkehitriny = ankehitriny.toISOString().split('T')[0];
        datyInput.value = datyAnkehitriny;
    }

    // Fitantanana ny animation ho an'ny karatra
    const karatra = document.querySelectorAll('.karatra');
    karatra.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });

    // Fitantanana ny hover effects ho an'ny bokotra
    const bokotra = document.querySelectorAll('.btn');
    bokotra.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Fitantanana ny form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--loko-mena)';
                    field.addEventListener('input', function() {
                        this.style.borderColor = '';
                    });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                hampisehoHafatra('Fenoy ny saha rehetra takiana', 'hadisoana');
            }
        });
    });
});

// Function hampiseho hafatra
function hampisehoHafatra(hafatra, karazana = 'fampandrenesana') {
    const fampandrenesana = document.createElement('div');
    fampandrenesana.className = `fampandrenesana fampandrenesana-${karazana}`;
    
    const icon = karazana === 'fahombiazana' ? 'fas fa-check-circle' : 
                 karazana === 'hadisoana' ? 'fas fa-exclamation-circle' : 
                 'fas fa-info-circle';
    
    fampandrenesana.innerHTML = `
        <i class="${icon}"></i>
        <span>${hafatra}</span>
    `;
    
    document.body.appendChild(fampandrenesana);
    
    setTimeout(() => {
        fampandrenesana.remove();
    }, 5000);
}

// Function formatFileSize
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}