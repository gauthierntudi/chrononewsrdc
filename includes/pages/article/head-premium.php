    <style>
        img:is([sizes="auto" i], [sizes^="auto," i]) { 
            contain-intrinsic-size: 3000px 1500px 
        }
        
        /* Styles Premium */
        .premium-blur { 
            filter: blur(8px); 
            user-select: none; 
            pointer-events: none;
            mask-image: linear-gradient(to bottom, black 0%, transparent 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 0%, transparent 100%);
            opacity: 0.7;
        }
        .premium-lock-overlay {
            position: relative;
            margin-top: -300px;
            z-index: 20;
            padding: 40px 20px;
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 20%, rgba(255,255,255,1) 100%);
            display: flex;
            justify-content: center;
        }
        .premium-lock-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            border: 1px solid #e2e8f0;
        }
        .premium-lock-icon {
            width: 60px;
            height: 60px;
            background: #fff7ed;
            color: #ea580c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
        }
        .premium-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
        }
        @media (max-width: 600px) {
            .premium-options { grid-template-columns: 1fr; }
        }
        .premium-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 14px;
            min-height: 70px; /* Hauteur suffisante pour 2 lignes */
            line-height: 1.4;
        }
        .btn-buy {
            background: #484747;
            color: #ffffff;
            border: none;
        }
        .btn-buy:hover { background: #333333; }
        .btn-sub {
            background: #3cc203;
            color: white;
            border: none;
        }
        .btn-sub:hover { background: #2b8704; }
    </style>
