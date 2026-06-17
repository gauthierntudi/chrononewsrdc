         /* Custom Contact Form Styles */
         .kmb-contact-form-wrapper {
             background: #ffffff;
             padding: 40px;
             border-radius: 12px;
             box-shadow: 0 10px 30px rgba(0,0,0,0.05);
             margin: 30px 0;
             border: 1px solid #f0f0f0;
         }
         /* Ensure CF7 wrappers don't break layout */
         .kmb-contact-form-wrapper .wpcf7-form-control-wrap {
             display: block;
             width: 100%;
         }
         .kmb-form-row {
             display: flex;
             gap: 20px;
             margin-bottom: 25px;
         }
         .kmb-form-col {
             flex: 1;
         }
         @media (max-width: 768px) {
             .kmb-form-row {
                 flex-direction: column;
                 gap: 0;
                 margin-bottom: 0;
             }
             .kmb-form-col {
                 margin-bottom: 25px;
             }
         }
         .kmb-form-group {
             margin-bottom: 25px;
             position: relative;
         }
         .kmb-form-label {
             display: block;
             margin-bottom: 8px;
             font-weight: 600;
             color: #333;
             font-size: 0.95rem;
         }
         .kmb-form-input, .kmb-form-textarea, .kmb-form-select {
             display: block; /* Ensure block display for full width */
             width: 100%;
             padding: 12px 15px;
             border: 2px solid #eef2f5;
             border-radius: 8px;
             font-size: 1rem;
             transition: all 0.3s ease;
             background: #fcfcfc;
             color: #333;
             box-sizing: border-box;
         }
         /* Force height equality for inputs and selects */
         .kmb-form-input, .kmb-form-select {
             height: 50px;
             line-height: normal;
         }
         /* Custom arrow for select to ensure consistent rendering */
         .kmb-form-select {
             appearance: none;
             -webkit-appearance: none;
             -moz-appearance: none;
             background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
             background-repeat: no-repeat;
             background-position: right 15px center;
             background-size: 16px;
             padding-right: 40px;
             
             /* FORCE FULL WIDTH */
             display: block !important;
             width: 100% !important;
             min-width: 100% !important;
             max-width: 100% !important;
             box-sizing: border-box !important;
         }
         .kmb-form-input:focus, .kmb-form-textarea:focus, .kmb-form-select:focus {
             border-color: #2490e2;
             background: #fff;
             outline: none;
             box-shadow: 0 0 0 4px rgba(36, 144, 226, 0.1);
         }
         .kmb-form-textarea {
             min-height: 150px;
             resize: vertical;
         }
         .kmb-submit-btn {
             background: linear-gradient(135deg, #2490e2 0%, #1a7ac4 100%);
             color: white;
             padding: 15px 35px;
             border: none;
             border-radius: 0px; /* Demandé : 0px */
             font-size: 1rem;
             font-weight: 700;
             cursor: pointer;
             transition: all 0.3s ease;
             display: inline-flex;
             align-items: center;
             justify-content: center;
             box-shadow: none; /* Demandé : pas de shadow */
             text-transform: uppercase;
             letter-spacing: 0.5px;
         }
         .kmb-submit-btn:hover {
             transform: translateY(-2px);
             background: linear-gradient(135deg, #2b9bf0 0%, #2085d6 100%);
         }
         
         /* Pour le champ "Autre sujet" masqué par défaut */
         #other-subject-group {
             display: none;
             margin-top: 15px;
             animation: fadeIn 0.3s ease-in-out;
         }
         @keyframes fadeIn {
             from { opacity: 0; transform: translateY(-10px); }
             to { opacity: 1; transform: translateY(0); }
         }
         
         .logo_small_wrapper_table .logo_small_wrapper .logo_link > h1,
         .logo_small_wrapper_table .logo_small_wrapper .logo_link > span {
             display: flex;
             margin: 0px;
             padding: 0px;
         } /* elementor category */
         .elementor-widget-wp-widget-categories h5 {
             display: none;
         }
         .elementor-widget-wp-widget-categories ul {
             list-style: none;
             padding: 0px 0px 0px 15px !important;
             margin: 0px;
             display: flex;
             flex-direction: column;
             gap: 7px;
         }
         .elementor-widget-wp-widget-categories ul li {
             margin-bottom: 0 !important;
             list-style: none;
             font-family: var(--jl-menu-font);
             font-size: 14px;
             font-weight: var(--jl-cat-font-weight);
             display: flex;
             flex-direction: column;
             gap: 7px;
         }
         .elementor-widget-wp-widget-categories ul li a {
             display: inline-flex;
             align-items: center;
             width: 100%;
         }
         .elementor-widget-wp-widget-categories ul li a:before {
             content: "";
             position: absolute;
             margin-left: -15px;
             border: solid currentcolor;
             border-width: 0 1px 1px 0;
             display: inline-block;
             padding: 2px;
             vertical-align: middle;
             transform: rotate(-45deg);
             -webkit-transform: rotate(-45deg);
         }
         .elementor-widget-wp-widget-categories span {
             margin-right: 0px;
             margin-left: auto;
             color: #fff;
             text-align: center;
             min-width: 24px;
             height: 24px;
             line-height: 24px;
             border-radius: 4px;
             padding: 0px 5px;
             font-size: 80%;
         }

            .wpcf7-response-output.is-error {
              position: relative;
              padding: 16px 20px 16px 52px;
              margin: 16px 0;
              border-radius: 10px;
              background: #fff1f2;
              color: #881337;
              font-size: 14px;
              border: 1px solid #fecdd3;
            }

            .wpcf7-response-output.is-error::before {
              content: "⚠";
              position: absolute;
              left: 18px;
              top: 50%;
              transform: translateY(-50%);
              font-size: 20px;
              color: #dc2626;
            }

            .wpcf7-response-output.is-success {
              position: relative;
              padding: 16px 20px 16px 52px;
              margin: 16px 0;
              border-radius: 10px;
              background: #ecfdf5;
              color: #065f46;
              font-size: 14px;
              border: 1px solid #a7f3d0;
            }

            .wpcf7-response-output.is-success::before {
              content: "✔";
              position: absolute;
              left: 18px;
              top: 50%;
              transform: translateY(-50%);
              font-size: 18px;
              color: #22c55e;
            }


            .wpcf7-response-output.is-error {
              animation: slideFadeIn 0.4s ease-out;
            }

            .wpcf7-response-output.is-success {
              animation: slideFadeIn 0.4s ease-out;
            }

            @keyframes slideFadeIn {
              from {
                opacity: 0;
                transform: translateY(-5px);
              }
              to {
                opacity: 1;
                transform: translateY(0);
              }
            }

            /* Spinner for button */
            .kmb-spinner {
                display: inline-block;
                width: 1.2rem;
                height: 1.2rem;
                vertical-align: text-bottom;
                border: 2px solid currentColor;
                border-right-color: transparent;
                border-radius: 50%;
                animation: kmb-spinner-border .75s linear infinite;
                margin-right: 10px;
            }

            @keyframes kmb-spinner-border {
                to { transform: rotate(360deg); }
            }
