/**
 * ============================================================================
 * useShadow - Hook per gestire l'ombra a terra dell'avatar
 * ============================================================================
 *
 * COME FUNZIONA CONTACTSHADOWS
 * ----------------------------
 * ContactShadows di @react-three/drei crea un'ombra "baked" su un piano invisibile.
 * È diversa dalle shadow maps tradizionali:
 *
 * - NON richiede configurazione luci (funziona con qualsiasi illuminazione)
 * - Renderizza la scena dall'alto su una texture
 * - Applica blur per effetto morbido
 * - Ottima per avatar e prodotti e-commerce
 *
 * PARAMETRI PRINCIPALI
 * --------------------
 * - position: [x, y, z] - dove posizionare il piano ombra (y = altezza terreno)
 * - opacity: 0-1 - quanto è scura l'ombra
 * - blur: 0-10 - quanto è sfumata (valori alti = più morbida)
 * - far: distanza massima per calcolare l'ombra
 * - resolution: qualità texture (256, 512, 1024)
 * - color: colore dell'ombra (default nero)
 *
 * ESEMPIO USO
 * -----------
 * const { shadowProps, ShadowComponent } = useShadow({ enabled: true, blur: 2 });
 * // In JSX: <ShadowComponent />
 *
 * ============================================================================
 */

import React, { useMemo } from 'react';
import { ContactShadows } from '@react-three/drei';

/**
 * Configurazioni preset per diversi tipi di ombra
 */
export const SHADOW_PRESETS = {
  // Ombra morbida e leggera (default)
  soft: {
    opacity: 0.4,
    blur: 2.5,
    far: 4,
    resolution: 512,
  },
  // Ombra più definita
  sharp: {
    opacity: 0.6,
    blur: 1,
    far: 4,
    resolution: 1024,
  },
  // Ombra molto morbida e diffusa
  diffuse: {
    opacity: 0.3,
    blur: 4,
    far: 6,
    resolution: 512,
  },
  // Ombra per avatar a figura intera
  fullBody: {
    opacity: 0.5,
    blur: 2,
    far: 3,
    resolution: 512,
  },
  // Nessuna ombra
  none: null,
};

/**
 * Hook per configurare l'ombra a terra dell'avatar
 *
 * @param {Object} options - Opzioni configurazione
 * @param {boolean} options.enabled - Abilita/disabilita ombra (default: true)
 * @param {string} options.preset - Preset da usare: 'soft', 'sharp', 'diffuse', 'fullBody' (default: 'soft')
 * @param {number} options.groundY - Altezza del terreno (default: -1)
 * @param {number} options.opacity - Opacità ombra 0-1 (sovrascrive preset)
 * @param {number} options.blur - Sfumatura ombra (sovrascrive preset)
 * @param {number} options.far - Distanza calcolo ombra (sovrascrive preset)
 * @param {number} options.resolution - Risoluzione texture (sovrascrive preset)
 * @param {string} options.color - Colore ombra (default: 'black')
 * @param {number} options.width - Larghezza area ombra (default: 10)
 * @param {number} options.height - Profondità area ombra (default: 10)
 *
 * @returns {Object} { shadowProps, ShadowComponent, enabled }
 *
 * @example
 * // Uso base
 * const { ShadowComponent } = useShadow();
 * // JSX: {ShadowComponent && <ShadowComponent />}
 *
 * @example
 * // Con preset
 * const { ShadowComponent } = useShadow({ preset: 'sharp', groundY: -0.5 });
 *
 * @example
 * // Personalizzato
 * const { ShadowComponent } = useShadow({
 *   opacity: 0.5,
 *   blur: 3,
 *   groundY: -1,
 * });
 */
export function useShadow(options = {}) {
  const {
    enabled = true,
    preset = 'soft',
    groundY = -1,
    opacity,
    blur,
    far,
    resolution,
    color = 'black',
    width = 10,
    height = 10,
  } = options;

  // Calcola le props finali combinando preset + override
  const shadowProps = useMemo(() => {
    if (!enabled) return null;

    const presetConfig = SHADOW_PRESETS[preset] || SHADOW_PRESETS.soft;
    if (!presetConfig) return null;

    return {
      position: [0, groundY, 0],
      opacity: opacity ?? presetConfig.opacity,
      blur: blur ?? presetConfig.blur,
      far: far ?? presetConfig.far,
      resolution: resolution ?? presetConfig.resolution,
      color,
      width,
      height,
      // Rotazione per orientare il piano correttamente
      rotation: [-Math.PI / 2, 0, 0],
    };
  }, [enabled, preset, groundY, opacity, blur, far, resolution, color, width, height]);

  // Componente pronto da renderizzare
  // Nota: usiamo React.createElement invece di JSX perché questo è un file .js
  const ShadowComponent = useMemo(() => {
    if (!shadowProps) return null;
      // Ritorna una funzione componente
    return function AvatarShadow() {
      return React.createElement(ContactShadows, {
        position: shadowProps.position,
        opacity: shadowProps.opacity,
        blur: shadowProps.blur,
        far: shadowProps.far,
        resolution: shadowProps.resolution,
        color: shadowProps.color,
        width: shadowProps.width,
        height: shadowProps.height,
        frames: 1, // Renderizza una volta (performance)
        scale: 10, // Scala del piano ombra
      });
    };
  }, [shadowProps]);

  return {
    shadowProps,
    ShadowComponent,
    enabled: !!shadowProps,
  };
}

export default useShadow;