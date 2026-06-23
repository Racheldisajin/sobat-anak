@php
    $gameSlug = $gameSlug ?? ($gameSetting->slug ?? 'game');
@endphp
<script>
(function(){
    const GAME_SLUG = @json($gameSlug);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const ROOT = document.getElementById('root') || document.body;
    const EXTRA_ROOTS = new Set();
    let userStarted = false;
    let alreadyAwarded = false;
    let isPosting = false;
    let lastAwardAt = 0;

    function formatNumber(value){
        return Number(value || 0).toLocaleString('id-ID');
    }

    function syncPoints(points){
        document.querySelectorAll('[data-points], [data-points-bottom]').forEach(function(el){
            el.textContent = formatNumber(points);
        });
    }

    function showGamePointToast(message, success = true){
        let toast = document.getElementById('sobat-game-point-toast');
        if(!toast){
            toast = document.createElement('div');
            toast.id = 'sobat-game-point-toast';
            toast.style.position = 'fixed';
            toast.style.left = '50%';
            toast.style.bottom = '24px';
            toast.style.transform = 'translateX(-50%)';
            toast.style.zIndex = '99999';
            toast.style.maxWidth = 'min(92vw, 440px)';
            toast.style.padding = '14px 18px';
            toast.style.borderRadius = '22px';
            toast.style.fontWeight = '900';
            toast.style.textAlign = 'center';
            toast.style.boxShadow = '0 18px 45px rgba(42,61,60,.18)';
            toast.style.transition = 'all .25s ease';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.background = success ? '#44C3B5' : '#F3716B';
        toast.style.color = '#fff';
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'auto';
        clearTimeout(toast._hideTimer);
        toast._hideTimer = setTimeout(function(){
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
        }, 2800);
    }

    function extractScore(){
        const scoreSelectors = [
            '[data-score]', '[data-game-score]', '#score', '#final-score', '.score', '.final-score'
        ];
        for(const selector of scoreSelectors){
            const el = document.querySelector(selector);
            if(el){
                const n = parseInt(String(el.textContent || el.value || '').replace(/[^0-9]/g, ''), 10);
                if(!Number.isNaN(n)) return n;
            }
        }

        let text = (document.body.innerText || '').replace(/\s+/g, ' ');
        EXTRA_ROOTS.forEach(function(root){
            try { text += ' ' + (root.innerText || '').replace(/\s+/g, ' '); } catch(e){}
        });
        const patterns = [
            /skor\s*akhir\D{0,40}(\d+)/i,
            /total\s*skor\D{0,40}(\d+)/i,
            /skor\s*kamu\D{0,40}(\d+)/i,
            /score\D{0,40}(\d+)/i,
            /skor\D{0,25}(\d+)/i
        ];
        for(const pattern of patterns){
            const match = text.match(pattern);
            if(match && match[1]) return parseInt(match[1], 10) || 0;
        }
        return 0;
    }

    async function awardPoints(payload = {}){
        userStarted = true;
        if(alreadyAwarded || isPosting) return;

        // Anti double submit karena MutationObserver bisa terpanggil berkali-kali.
        const now = Date.now();
        if(now - lastAwardAt < 1500) return;
        lastAwardAt = now;

        alreadyAwarded = true;
        isPosting = true;

        const score = Math.max(0, parseInt(payload.score ?? extractScore() ?? 0, 10) || 0);

        try{
            const response = await fetch(@json(route('game.play')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ game: GAME_SLUG, score })
            });

            const data = await response.json().catch(function(){ return {}; });

            if(!response.ok || !data.ok){
                alreadyAwarded = false;
                if(data.redirect){
                    showGamePointToast('Login dulu agar poin game tersimpan ke akun kamu.', false);
                    return;
                }
                showGamePointToast(data.message || 'Poin belum berhasil tersimpan.', false);
                return;
            }

            syncPoints(data.points);
            showGamePointToast(data.message || ('Yeay! Kamu dapat '+(data.earned || 0)+' poin 🎉'), true);
        } catch(error){
            alreadyAwarded = false;
            console.warn('SobatAnak game point error:', error);
            showGamePointToast('Poin belum tersimpan. Coba main lagi setelah login.', false);
        } finally {
            isPosting = false;
        }
    }

    function resetAwardState(){
        userStarted = true;
        alreadyAwarded = false;
        isPosting = false;
    }

    function containsFinishText(){
        if(!userStarted) return false;
        let text = (ROOT.innerText || document.body.innerText || '').toLowerCase();
        EXTRA_ROOTS.forEach(function(root){
            try { text += ' ' + (root.innerText || '').toLowerCase(); } catch(e){}
        });
        const finishWords = [
            'game selesai', 'skor akhir', 'total skor', 'kamu menang', 'selamat',
            'berhasil', 'waktu habis', 'permainan selesai', 'hasil akhir', 'finish', 'finished', 'congratulation', 'congrats'
        ];
        return finishWords.some(function(word){ return text.includes(word); });
    }

    document.addEventListener('click', function(event){
        userStarted = true;
        const label = (event.target?.innerText || event.target?.textContent || '').toLowerCase().trim();
        if(!label) return;

        if(label.includes('main lagi') || label.includes('restart') || label.includes('ulang') || label.includes('mulai main') || label.includes('mulai')){
            resetAwardState();
        }

        if(label.includes('selesai') || label.includes('simpan karya') || label.includes('finish') || label.includes('done')){
            setTimeout(function(){ awardPoints({ score: extractScore() }); }, 700);
        }
    }, true);

    document.addEventListener('keydown', function(){ userStarted = true; }, true);
    document.addEventListener('touchstart', function(){ userStarted = true; }, {passive:true, capture:true});

    window.SobatAnakGamePoints = {
        award: awardPoints,
        reset: resetAwardState,
        game: GAME_SLUG
    };

    window.addEventListener('message', function(event){
        const data = event.data || {};
        if(typeof data !== 'object') return;

        const type = String(data.type || data.event || '').toLowerCase();
        if(type.includes('game') && (type.includes('finish') || type.includes('complete') || type.includes('win') || type.includes('done'))){
            awardPoints({ score: data.score || data.points || 0 });
        }
    });



    function attachExtraRoot(root){
        if(!root || EXTRA_ROOTS.has(root)) return;
        EXTRA_ROOTS.add(root);
        root.addEventListener('click', function(event){
            userStarted = true;
            const label = (event.target?.innerText || event.target?.textContent || '').toLowerCase().trim();
            if(label.includes('main lagi') || label.includes('restart') || label.includes('ulang') || label.includes('mulai')) resetAwardState();
            if(label.includes('selesai') || label.includes('simpan karya') || label.includes('finish') || label.includes('done')){
                setTimeout(function(){ awardPoints({ score: extractScore() }); }, 700);
            }
        }, true);
        const extraObserver = new MutationObserver(function(){
            if(alreadyAwarded || isPosting) return;
            if(containsFinishText()){
                setTimeout(function(){
                    if(containsFinishText()) awardPoints({ score: extractScore() });
                }, 900);
            }
        });
        extraObserver.observe(root, { childList:true, subtree:true, characterData:true });
    }

    function attachSameOriginIframes(){
        document.querySelectorAll('iframe').forEach(function(frame){
            const hook = function(){
                try {
                    const body = frame.contentDocument && frame.contentDocument.body;
                    if(body) attachExtraRoot(body);
                } catch(error) {
                    console.warn('SobatAnak tidak bisa membaca iframe game:', error);
                }
            };
            frame.addEventListener('load', hook);
            setTimeout(hook, 500);
            setTimeout(hook, 1500);
        });
    }

    const observer = new MutationObserver(function(){
        if(alreadyAwarded || isPosting) return;
        if(containsFinishText()){
            setTimeout(function(){
                if(containsFinishText()) awardPoints({ score: extractScore() });
            }, 900);
        }
    });

    observer.observe(ROOT, { childList:true, subtree:true, characterData:true });
    attachSameOriginIframes();
})();
</script>
