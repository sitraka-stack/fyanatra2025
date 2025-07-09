// ===== SCRIPT MANOKANA HO AN'NY MPAMPIANATRA =====

document.addEventListener('DOMContentLoaded', function() {
    // Fampisehoana ny fampandrenesana
    const fampandrenesana = document.querySelectorAll('.fampandrenesana');
    fampandrenesana.forEach(function(element) {
        element.classList.add('fade-in');
        
        // Afenina aorian'ny 5 segondra
        setTimeout(function() {
            element.style.opacity = '0';
            element.style.transform = 'translateX(100px)';
            setTimeout(function() {
                element.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // Fampisehoana ny karatra misy animation
    const karatra = document.querySelectorAll('.karatra');
    karatra.forEach(function(element, index) {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(function() {
            element.style.transition = 'all 0.6s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Fanamarinana ny form naoty
    const formNaoty = document.querySelector('form[action="mampiditra_naoty.php"]');
    if (formNaoty) {
        formNaoty.addEventListener('submit', function(e) {
            const naoty = document.getElementById('naoty').value;
            const mpianatra = document.getElementById('mpianatra').value;
            
            if (!mpianatra.trim()) {
                e.preventDefault();
                alert('Ampidiro ny anaran\'ny mpianatra');
                return;
            }
            
            if (naoty < 0 || naoty > 20) {
                e.preventDefault();
                alert('Ny naoty dia tokony ho eo anelanelan\'ny 0 sy 20');
                return;
            }
            
            // Fanamarinana fanampiny
            if (confirm('Azo antoka ve ny naoty ' + naoty + '/20 ho an\'i ' + mpianatra + '?')) {
                return true;
            } else {
                e.preventDefault();
            }
        });
    }
    
    // Fanamarinana ny form asa
    const formAsa = document.querySelector('form[action="mampiditra_asa.php"]');
    if (formAsa) {
        formAsa.addEventListener('submit', function(e) {
            const lohateny = document.getElementById('lohateny').value;
            const votoaty = document.getElementById('votoaty').value;
            const daty_farany = document.getElementById('daty_farany').value;
            
            if (!lohateny.trim() || !votoaty.trim() || !daty_farany) {
                e.preventDefault();
                alert('Fenoina daholo ny sehatra ilaina');
                return;
            }
            
            // Fanamarinana ny daty
            const daty_farany_obj = new Date(daty_farany);
            const ankehitriny = new Date();
            
            if (daty_farany_obj <= ankehitriny) {
                e.preventDefault();
                alert('Ny daty farany dia tokony ho any aoriana');
                return;
            }
            
            // Fanamarinana fanampiny
            if (confirm('Alefa ve ny asa "' + lohateny + '"?')) {
                return true;
            } else {
                e.preventDefault();
            }
        });
    }
    
    // Fitantanana ny rakitra
    const rakitraInput = document.getElementById('rakitra');
    if (rakitraInput) {
        rakitraInput.addEventListener('change', function(e) {
            const rakitra = e.target.files[0];
            
            if (rakitra) {
                // Fanamarinana ny habe
                if (rakitra.size > 10 * 1024 * 1024) {
                    alert('Ny habe ny rakitra dia mihoatra ny 10MB');
                    e.target.value = '';
                    return;
                }
                
                // Fanamarinana ny karazana
                const extension = rakitra.name.split('.').pop().toLowerCase();
                const karazana_ekena = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'];
                
                if (!karazana_ekena.includes(extension)) {
                    alert('Karazana rakitra tsy ekena');
                    e.target.value = '';
                    return;
                }
                
                // Mampiseho ny anaran'ny rakitra
                console.log('Rakitra voafidy: ' + rakitra.name);
            }
        });
    }
    
    // Fampisehoana ny toe-javatra fampianarana
    const fampianarana = document.querySelectorAll('.karatra-fampianarana');
    fampianarana.forEach(function(element) {
        if (element.classList.contains('mandeha')) {
            element.style.borderLeft = '4px solid #28a745';
        } else if (element.classList.contains('ho-avy')) {
            element.style.borderLeft = '4px solid #ffc107';
        } else if (element.classList.contains('vita')) {
            element.style.borderLeft = '4px solid #6c757d';
        }
    });
    
    // Fampisehoana ny salan-isa naoty
    const naoty_elements = document.querySelectorAll('.naoty-lehibe');
    naoty_elements.forEach(function(element) {
        const naoty = parseFloat(element.textContent);
        
        if (naoty >= 16) {
            element.classList.add('excellent');
        } else if (naoty >= 14) {
            element.classList.add('good');
        } else if (naoty >= 12) {
            element.classList.add('average');
        } else if (naoty >= 10) {
            element.classList.add('poor');
        } else {
            element.classList.add('very-poor');
        }
    });
    
    // Smooth scrolling ho an'ny rohy
    const rohy = document.querySelectorAll('a[href^="#"]');
    rohy.forEach(function(rohy) {
        rohy.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// ===== FUNCTIONS UTILITY =====

// Fampisehoana fampandrenesana
function mampiseho_fampandrenesana(hafatra, karazana = 'fahombiazana') {
    const fampandrenesana = document.createElement('div');
    fampandrenesana.className = `fampandrenesana fampandrenesana-${karazana}`;
    fampandrenesana.innerHTML = `
        <i class="fas fa-${karazana === 'fahombiazana' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${hafatra}
    `;
    
    document.body.appendChild(fampandrenesana);
    
    // Afenina aorian'ny 5 segondra
    setTimeout(function() {
        fampandrenesana.style.opacity = '0';
        fampandrenesana.style.transform = 'translateX(100px)';
        setTimeout(function() {
            document.body.removeChild(fampandrenesana);
        }, 300);
    }, 5000);
}

// Fanamarinana ny naoty
function manamafy_naoty(naoty) {
    const naoty_num = parseFloat(naoty);
    
    if (naoty_num >= 16) return 'Tsara indrindra';
    if (naoty_num >= 14) return 'Tsara';
    if (naoty_num >= 12) return 'Ahafahana';
    if (naoty_num >= 10) return 'Antonony';
    return 'Mbola mila ezaka';
}

// Fandikana ny daty ho malagasy
function daty_malagasy(daty) {
    const andro = ['Alahady', 'Alatsinainy', 'Talata', 'Alarobia', 'Alakamisy', 'Zoma', 'Sabotsy'];
    const volana = ['Janoary', 'Febroary', 'Martsa', 'Aprily', 'Mey', 'Jona', 'Jolay', 'Aogositra', 'Septambra', 'Oktobra', 'Novambra', 'Desambra'];
    
    const daty_obj = new Date(daty);
    const andro_anarana = andro[daty_obj.getDay()];
    const volana_anarana = volana[daty_obj.getMonth()];
    
    return `${andro_anarana} ${daty_obj.getDate()} ${volana_anarana} ${daty_obj.getFullYear()}`;
}

// Fandikana ny ora
function ora_malagasy(ora) {
    const ora_obj = new Date('2000-01-01 ' + ora);
    const ora_num = ora_obj.getHours();
    const minitra = ora_obj.getMinutes();
    
    return `${ora_num.toString().padStart(2, '0')}:${minitra.toString().padStart(2, '0')}`;
}