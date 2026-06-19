    <style>
        @import url('/css/brand.css');

        img:is([sizes="auto" i], [sizes^="auto," i]) {
            contain-intrinsic-size: 3000px 1500px;
        }

        /* ——— Paywall premium (charte CHRONONEWS) ——— */
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
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 20%, rgba(255, 255, 255, 1) 100%);
            display: flex;
            justify-content: center;
        }

        .premium-lock-card {
            background: var(--cn-white);
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 12px 40px rgba(17, 17, 17, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            border: 1px solid var(--cn-border);
            border-top: 4px solid var(--cn-red);
            font-family: var(--cn-font-body);
        }

        .premium-lock-icon {
            width: 60px;
            height: 60px;
            background: var(--cn-red-soft);
            color: var(--cn-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
        }

        .premium-lock-title {
            font-family: var(--cn-font-display);
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--cn-black);
            margin: 0 0 10px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .premium-lock-desc {
            color: var(--cn-text-muted);
            font-size: 0.9375rem;
            line-height: 1.55;
            margin: 0;
        }

        .premium-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 26px;
        }

        @media (max-width: 600px) {
            .premium-options {
                grid-template-columns: 1fr;
            }
        }

        .premium-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 18px;
            border-radius: 10px;
            font-family: var(--cn-font-body);
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            border: none;
            font-size: 0.875rem;
            min-height: 72px;
            line-height: 1.35;
            letter-spacing: 0.02em;
        }

        .premium-btn:focus-visible {
            outline: 2px solid var(--cn-blue);
            outline-offset: 2px;
        }

        /* CTA principal — abonnement */
        .btn-sub {
            background: var(--cn-red);
            color: var(--cn-white);
            box-shadow: 0 4px 16px rgba(209, 24, 16, 0.28);
        }

        .btn-sub:hover {
            background: var(--cn-red-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(209, 24, 16, 0.34);
        }

        .btn-sub .premium-btn-sub {
            font-size: 0.75rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.92);
            opacity: 1;
        }

        /* Action secondaire — achat à l'unité */
        .btn-buy {
            background: var(--cn-blue);
            color: var(--cn-white);
            box-shadow: 0 4px 16px rgba(30, 94, 255, 0.22);
        }

        .btn-buy:hover {
            background: #184bcc;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 94, 255, 0.28);
        }

        .btn-buy .premium-btn-price {
            font-size: 1.125rem;
            font-weight: 800;
            font-family: var(--cn-font-display);
        }

        .premium-lock-login {
            margin-top: 20px;
            font-size: 0.8125rem;
            color: var(--cn-text-muted);
        }

        .premium-lock-login a {
            color: var(--cn-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .premium-lock-login a:hover {
            color: var(--cn-red);
            text-decoration: underline;
        }
    </style>
