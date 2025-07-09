// Script ho an'ny agenda feno
document.addEventListener('DOMContentLoaded', function() {
    fanomezana_agenda();
    fanavaozana_ora_ankehitriny();
    
    // Manavao ny ora isaky ny minitra
    setInterval(fanavaozana_ora_ankehitriny, 60000);
});

function fanomezana_agenda() {
    // Animation fidirana ho an'ny tsanganana andro
    const tsanganana_andro = document.querySelectorAll('.day-column');
    tsanganana_andro.forEach((column, index) => {
        column.style.opacity = '0';
        column.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            column.style.transition = 'all 0.8s ease';
            column.style.opacity = '1';
            column.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animation ho an'ny fampianarana
    const singa_fampianarana = document.querySelectorAll('.agenda-course-item');
    singa_fampianarana.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.6s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, 500 + (index * 50));
    });
    
    // Mandehana mankany amin'ny andro ankehitriny
    mandehana_amin_andro_ankehitriny();
}

function fanavaozana_ora_ankehitriny() {
    const ankehitriny = new Date();
    const ora_ankehitriny = ankehitriny.getHours().toString().padStart(2, '0') + ':' + 
                       ankehitriny.getMinutes().toString().padStart(2, '0');
    
    // Manavao ny status fampianarana amin'ny agenda
    fanavaozana_status_fampianarana_agenda(ora_ankehitriny);
}

function fanavaozana_status_fampianarana_agenda(ora_ankehitriny) {
    const singa_fampianarana = document.querySelectorAll('.agenda-course-item');
    
    singa_fampianarana.forEach(item => {
        const fotoana_slot = item.querySelector('.course-time-slot');
        if (!fotoana_slot) return;
        
        const soratra_fotoana = fotoana_slot.textContent.trim();
        const fotoana = soratra_fotoana.match(/(\d{2}:\d{2})/g);
        
        if (fotoana && fotoana.length >= 2) {
            const ora_fanombohana = fotoana[0];
            const ora_famaranana = fotoana[1];
            
            // Manamarina raha anio
            const tsanganana_andro = item.closest('.day-column');
            const anio_ve = tsanganana_andro && tsanganana_andro.classList.contains('current-day');
            
            if (anio_ve) {
                // Manala ny kilasy status taloha
                item.classList.remove('current', 'upcoming', 'past');
                
                // Manala ny famantarana live taloha
                const famantarana_live_taloha = item.querySelector('.live-indicator');
                if (famantarana_live_taloha) {
                    famantarana_live_taloha.remove();
                }
                
                if (ora_ankehitriny >= ora_fanombohana && ora_ankehitriny <= ora_famaranana) {
                    // Fampianarana mandeha
                    item.classList.add('current');
                    
                    // Manampy famantarana live
                    const famantarana_live = document.createElement('div');
                    famantarana_live.className = 'live-indicator';
                    famantarana_live.innerHTML = '<i class="fas fa-circle"></i>';
                    item.appendChild(famantarana_live);
                    
                } else if (ora_ankehitriny < ora_fanombohana) {
                    // Fampianarana ho avy
                    item.classList.add('upcoming');
                } else {
                    // Fampianarana vita
                    item.classList.add('past');
                }
            }
        }
    });
}

function mandehana_amin_andro_ankehitriny() {
    const tsanganana_andro_ankehitriny = document.querySelector('.day-column.current-day');
    if (tsanganana_andro_ankehitriny) {
        setTimeout(() => {
            tsanganana_andro_ankehitriny.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }, 1000);
    }
}

function atontao_fandaharam_potoana() {
    // Manomana ny pejy ho an'ny fanontana
    const lohateny_tany_am_boalohany = document.title;
    document.title = 'Fandaharam-potoana - ' + lohateny_tany_am_boalohany;
    
    // Manafina ny singa tsy ilaina ho an'ny fanontana
    const singa_hafenina = document.querySelectorAll('.header-navigation, .header-actions, .schedule-summary');
    singa_hafenina.forEach(element => {
        element.style.display = 'none';
    });
    
    // Mandefa ny fanontana
    window.print();
    
    // Mamerina ny fampisehoana aorian'ny fanontana
    setTimeout(() => {
        document.title = lohateny_tany_am_boalohany;
        singa_hafenina.forEach(element => {
            element.style.display = '';
        });
    }, 1000);
}

// Fonction ho an'ny fampisehoana antsipiriany fampianarana amin'ny click
function asehoy_antsipiriany_fampianarana(singa_fampianarana) {
    const taranja = singa_fampianarana.querySelector('.course-subject-name').textContent;
    const fotoana_slot = singa_fampianarana.querySelector('.course-time-slot').textContent;
    const efitrano = singa_fampianarana.querySelector('.course-room-mini').textContent.replace(/.*\s/, '');
    const mpampianatra = singa_fampianarana.querySelector('.course-teacher-mini').textContent.replace(/.*\s/, '');
    
    const modal = document.createElement('div');
    modal.className = 'course-detail-modal';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="this.parentElement.remove()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>${taranja}</h3>
                <button class="modal-close" onclick="this.closest('.course-detail-modal').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="course-detail-item">
                    <i class="fas fa-clock"></i>
                    <span>Ora: ${fotoana_slot}</span>
                </div>
                <div class="course-detail-item">
                    <i class="fas fa-door-open"></i>
                    <span>Efitrano: ${efitrano}</span>
                </div>
                <div class="course-detail-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Mpampianatra: ${mpampianatra}</span>
                </div>
            </div>
        </div>
    `;
    
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    document.body.appendChild(modal);
}

// Manampy ny events click amin'ny fampianarana
document.addEventListener('click', function(e) {
    const singa_fampianarana = e.target.closest('.agenda-course-item');
    if (singa_fampianarana) {
        asehoy_antsipiriany_fampianarana(singa_fampianarana);
    }
});

// Fafintina keyboard
document.addEventListener('keydown', function(e) {
    // P ho an'ny fanontana
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        atontao_fandaharam_potoana();
    }
    
    // Échap ho an'ny fanidiana modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.course-detail-modal');
        modals.forEach(modal => modal.remove());
    }
});

// Styles ho an'ny modal antsipiriany fampianarana
const styles_modal = document.createElement('style');
styles_modal.textContent = `
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        animation: modalSlideIn 0.3s ease;
    }
    
    .modal-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #3b82f6 0%, #22c55e 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: background 0.3s ease;
    }
    
    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .course-detail-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 1.1rem;
    }
    
    .course-detail-item:last-child {
        border-bottom: none;
    }
    
    .course-detail-item i {
        color: #3b82f6;
        width: 20px;
        text-align: center;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
`;
document.head.appendChild(styles_modal);