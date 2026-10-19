{{-- Tokens y reglas de movimiento compartidas (filosofía de Emil Kowalski). Se incluye en los layouts Bootstrap. --}}
<style>
    :root {
        --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
        --ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
        --ease-drawer: cubic-bezier(0.32, 0.72, 0, 1);
        --duration-press: 160ms;
        --duration-fast: 150ms;
        --duration-base: 200ms;
    }

    /* Feedback al presionar: la interfaz "escucha" al usuario. */
    .btn {
        transition:
            color var(--duration-fast) ease,
            background-color var(--duration-fast) ease,
            border-color var(--duration-fast) ease,
            box-shadow var(--duration-fast) ease,
            transform var(--duration-press) var(--ease-out);
        -webkit-tap-highlight-color: transparent;
    }

    .btn:active:not(:disabled):not(.disabled) {
        transform: scale(0.97);
    }

    /* Dropdowns: entran desde su disparador, nunca desde scale(0). */
    .dropdown-menu {
        transform-origin: top left;
        transition:
            opacity var(--duration-fast) var(--ease-out),
            scale var(--duration-fast) var(--ease-out);
    }

    .dropdown-menu-end {
        transform-origin: top right;
    }

    @starting-style {
        .dropdown-menu.show {
            opacity: 0;
            scale: 0.96;
        }
    }

    /* Alertas flash: entrada suave, salida más rápida que la entrada. */
    .alert.fade {
        transition:
            opacity var(--duration-fast) var(--ease-out),
            translate var(--duration-fast) var(--ease-out);
    }

    .alert.fade.show {
        transition-duration: 250ms;
    }

    @starting-style {
        .alert.fade.show {
            opacity: 0;
            translate: 0 -6px;
        }
    }

    /* Colapso del navbar móvil: más ágil que los 350ms por defecto. */
    .collapsing {
        transition: height 250ms var(--ease-out);
    }

    /* Entrada escalonada para grupos de tarjetas (momentos poco frecuentes). */
    .reveal-stagger > * {
        animation: reveal-up 300ms var(--ease-out) both;
    }

    .reveal-stagger > *:nth-child(2) { animation-delay: 50ms; }
    .reveal-stagger > *:nth-child(3) { animation-delay: 100ms; }
    .reveal-stagger > *:nth-child(4) { animation-delay: 150ms; }
    .reveal-stagger > *:nth-child(5) { animation-delay: 200ms; }
    .reveal-stagger > *:nth-child(n+6) { animation-delay: 250ms; }

    @keyframes reveal-up {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
    }

    /* Movimiento reducido: conservamos opacidad, eliminamos desplazamientos. */
    @media (prefers-reduced-motion: reduce) {
        .btn:active:not(:disabled):not(.disabled) {
            transform: none;
        }

        .dropdown-menu {
            transition: opacity var(--duration-fast) ease;
        }

        @starting-style {
            .dropdown-menu.show { scale: 1; }
            .alert.fade.show { translate: 0 0; }
        }

        .reveal-stagger > * {
            animation-name: reveal-fade;
        }

        @keyframes reveal-fade {
            from { opacity: 0; }
        }
    }
</style>
