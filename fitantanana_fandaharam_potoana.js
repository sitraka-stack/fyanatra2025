// Script manokana ho an'ny fitantanana fandaharam-potoana

document.addEventListener('DOMContentLoaded', function() {
    fanomezana_fomba_fandaharam_potoana();
    fanomezana_fampahafantarana();
    fanomezana_animation_tabilao();
    fanomezana_fijerena_anio();
});

function fanomezana_fomba_fandaharam_potoana() {
    const fomba = document.getElementById('fomba_fandaharam_potoana');
    const safidy_taranja = document.getElementById('taranja');
    const fampisehoana_mpampianatra = document.getElementById('mpampianatra_aseho');
    const fidirana_ora_fanombohana = document.getElementById('ora_fanombohana');
    const fidirana_ora_famaranana = document.getElementById('ora_famaranana');

    // Famarinana ora amin'ny fotoana tena izy
    if (fidirana_ora_fanombohana && fidirana_ora_famaranana) {
        fidirana_ora_fanombohana.addEventListener('change', famarino_elanelana_ora);
        fidirana_ora_famaranana.addEventListener('change', famarino_elanelana_ora);
    }

    // Fanavaozana mpampianatra rehefa miova ny taranja
    if (safidy_taranja && fampisehoana_mpampianatra) {
        safidy_taranja.addEventListener('change', function() {
            if (this.value) {
                // Simulation ny fakana mpampianatra
                // Amin'ny tena izy, atao amin'ny server via LDAP
                fampisehoana_mpampianatra.value = 'Maka...';
                
                // Effet visuel loading
                setTimeout(() => {
                    fampisehoana_mpampianatra.value = 'Mpampianatra voatendry ho azy';
                }, 500);
            } else {
                fampisehoana_mpampianatra.value = '';
            }
        });
    }

    // Animation fandefasana fomba
    if (fomba) {
        fomba.addEventListener('submit', function(e) {
            const bokotra_fandefasana = fomba.querySelector('.btn-add-course');
            if (bokotra_fandefasana) {
                bokotra_fandefasana.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mampiditra...';
                bokotra_fandefasana.disabled = true;
                fomba.classList.add('loading');
            }
        });
    }
}

function famarino_elanelana_ora() {
    const ora_fanombohana = document.getElementById('ora_fanombohana').value;
    const ora_famaranana = document.getElementById('ora_famaranana').value;
    
    if (ora_fanombohana && ora_famaranana) {
        if (ora_fanombohana >= ora_famaranana) {
            asehoy_fampahafantarana_fandaharam_potoana('Ny ora fanombohana dia tokony ho mialoha ny ora famaranana', 'warning');
            document.getElementById('ora_famaranana').focus();
            return false;
        }
        
        // Manamarina ny faharetana faran'izay kely (30 minitra)
        const fanombohana = new Date('2000-01-01 ' + ora_fanombohana);
        const famaranana = new Date('2000-01-01 ' + ora_famaranana);
        const faharetana_minitra = (famaranana - fanombohana) / (1000 * 60);
        
        if (faharetana_minitra < 30) {
            asehoy_fampahafantarana_fandaharam_potoana('Ny faharetan\'ny fampianarana dia tokony ho 30 minitra faran\'izay kely', 'warning');
            return false;
        }
        
        if (faharetana_minitra > 240) {
            asehoy_fampahafantarana_fandaharam_potoana('Ny faharetan\'ny fampianarana dia tsy tokony hihoatra ny 4 ora', 'warning');
            return false;
        }
    }
    
    return true;
}

function avereno_fomba() {
    const fomba = document.getElementById('fomba_fandaharam_potoana');
    if (fomba) {
        fomba.reset();
        document.getElementById('mpampianatra_aseho').value = '';
        
        // Animation reset
        fomba.style.opacity = '0.5';
        setTimeout(() => {
            fomba.style.opacity = '1';
        }, 200);
        
        asehoy_fampahafantarana_fandaharam_potoana('Fomba naverina', 'info');
    }
}

function fanomezana_fampahafantarana() {
    // Auto-manafina ny fampahafantarana aorian'ny 5 segondra
    const fampahafantarana = document.querySelectorAll('.notification');
    fampahafantarana.forEach(notification => {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    });
}

function asehoy_fampahafantarana_fandaharam_potoana(hafatra, karazana = 'info') {
    // Manala ny fampahafantarana efa misy
    const fampahafantarana_efa_misy = document.querySelectorAll('.notification');
    fampahafantarana_efa_misy.forEach(notif => notif.remove());
    
    const fampahafantarana = document.createElement('div');
    fampahafantarana.className = `notification notification-${karazana}`;
    
    const sary_map = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    fampahafantarana.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${sary_map[karazana] || 'info-circle'}"></i>
            <span>${hafatra}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(fampahafantarana);
    
    // Auto-fafana aorian'ny 4 segondra
    setTimeout(() => {
        if (fampahafantarana.parentElement) {
            fampahafantarana.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => fampahafantarana.remove(), 300);
        }
    }, 4000);
}

function fanomezana_animation_tabilao() {
    // Animation amin'ny hover ny andalana tabilao
    const andalana_tabilao = document.querySelectorAll('.schedule-row');
    andalana_tabilao.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.zIndex = '10';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.zIndex = '1';
        });
    });
    
    // Animation ny badges amin'ny click
    const badges = document.querySelectorAll('.day-badge, .subject-name, .room-number');
    badges.forEach(badge => {
        badge.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
}

function fanomezana_fijerena_anio() {
    // Animation ny fampianarana anio
    const fampianarana_anio = document.querySelectorAll('.course-preview-today');
    fampianarana_anio.forEach((course, index) => {
        course.style.opacity = '0';
        course.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            course.style.transition = 'all 0.5s ease';
            course.style.opacity = '1';
            course.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Fonction ho an'ny fampisehoana/fanafenana tabilao fampianarana
function asehoy_tabilao_fampianarana() {
    const karatra_tabilao = document.getElementById('tabilao_fampianarana_karatra');
    
    if (karatra_tabilao.style.display === 'none' || karatra_tabilao.style.display === '') {
        // Asehoy ny tabilao
        karatra_tabilao.style.display = 'block';
        karatra_tabilao.classList.remove('hide');
        karatra_tabilao.classList.add('show');
        
        // Scroll mankany amin'ny tabilao
        setTimeout(() => {
            karatra_tabilao.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }, 100);
        
        asehoy_fampahafantarana_fandaharam_potoana('Tabilao fampianarana naseho', 'info');
    } else {
        // Afeno ny tabilao
        karatra_tabilao.classList.remove('show');
        karatra_tabilao.classList.add('hide');
        
        setTimeout(() => {
            karatra_tabilao.style.display = 'none';
        }, 500);
        
        asehoy_fampahafantarana_fandaharam_potoana('Tabilao fampianarana nafenina', 'info');
    }
}

// Fonction ho an'ny fanamarina fafana miaraka amin'ny modal
function famarino_fafana(id_fampianarana, anarana_kilasy, anarana_fampianarana) {
    const modal = document.createElement('div');
    modal.className = 'delete-modal';
    modal.innerHTML = `
        <div class="delete-modal-content">
            <div class="delete-modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Famarino ny fafana</h3>
            </div>
            <div class="delete-modal-body">
                <p>Tena hofafana ve ny fampianarana <strong>${anarana_fampianarana}</strong> ?</p>
                <p class="warning-text">Tsy azo averina ity asa ity.</p>
            </div>
            <div class="delete-modal-actions">
                <button class="btn btn-secondary" onclick="hidio_modal_fafana()">
                    <i class="fas fa-times"></i> Aoka ihany
                </button>
                <a href="fafao_fandaharam_potoana.php?id=${id_fampianarana}&kilasy=${encodeURIComponent(anarana_kilasy)}" 
                   class="btn btn-danger">
                    <i class="fas fa-trash"></i> Fafao
                </a>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

function hidio_modal_fafana() {
    const modal = document.querySelector('.delete-modal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

// Fafintina keyboard
document.addEventListener('keydown', function(e) {
    // Ctrl + N ho an'ny fampianarana vaovao
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        document.getElementById('andro').focus();
        asehoy_fampahafantarana_fandaharam_potoana('Mode fanampiana fampianarana navokatra', 'info');
    }
    
    // Echap ho an'ny fanidiana modals sy fampahafantarana
    if (e.key === 'Escape') {
        const fampahafantarana = document.querySelectorAll('.notification');
        fampahafantarana.forEach(notification => notification.remove());
        hidio_modal_fafana();
    }
    
    // Ctrl + R ho an'ny famerenana fomba
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        avereno_fomba();
    }
    
    // Ctrl + T ho an'ny fampisehoana/fanafenana tabilao
    if (e.ctrlKey && e.key === 't') {
        e.preventDefault();
        asehoy_tabilao_fampianarana();
    }
});

// Famarinana amin'ny fotoana tena izy ny fomba
document.addEventListener('input', function(e) {
    if (e.target.matches('#efitrano')) {
        // Formatting automatique ny anaran'ny efitrano
        let sanda = e.target.value.toUpperCase();
        // Manala ny litera tsy alphanumeric afa-tsy ny space sy tiret
        sanda = sanda.replace(/[^A-Z0-9\s\-]/g, '');
        e.target.value = sanda;
    }
});

// Fanatsarana UX miaraka amin'ny tooltips
function fanomezana_tooltips() {
    const singa_tooltip = document.querySelectorAll('[data-tooltip]');
    singa_tooltip.forEach(element => {
        element.addEventListener('mouseenter', asehoy_tooltip);
        element.addEventListener('mouseleave', afeno_tooltip);
    });
}

function asehoy_tooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = e.target.getAttribute('data-tooltip');
    tooltip.style.cssText = `
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.8rem;
        z-index: 1000;
        pointer-events: none;
        animation: fadeIn 0.2s ease;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
}

function afeno_tooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Manomboka ny tooltips amin'ny loading
document.addEventListener('DOMContentLoaded', fanomezana_tooltips);

// Fanavaozana ora amin'ny fotoana tena izy amin'ny fijerena anio
function fanavaozana_ora_ankehitriny() {
    const singa_ora = document.querySelectorAll('.current-time');
    const ankehitriny = new Date();
    const tady_ora = ankehitriny.toLocaleTimeString('mg-MG', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    singa_ora.forEach(element => {
        element.textContent = tady_ora;
    });
}

// Manavao ny ora isaky ny minitra
setInterval(fanavaozana_ora_ankehitriny, 60000);
fanavaozana_ora_ankehitriny(); // Antso voalohany