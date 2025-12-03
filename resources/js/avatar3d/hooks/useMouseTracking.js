import { useEffect, useRef, useCallback } from 'react';
import {
  findBones,
  updateHeadTracking,
  updateNeckTracking,
  updateArmTracking,
} from '../avatarBoneTracking';

/**
 * Hook per il tracking del mouse sull'avatar
 * Fa seguire testa, collo e braccia al movimento del mouse
 *
 * Options:
 * @param {boolean} head - Abilita tracking testa (default: true)
 * @param {boolean} neck - Abilita tracking collo (default: true)
 * @param {boolean} arms - Abilita tracking braccia (default: true)
 * @param {object} centerOffset - Offset manuale del centro { x: 0, y: 0 } (ignorato se autoCenter è true)
 * @param {object|null} localTrackingArea - Se definito, limita il tracking a un'area locale
 *   - canvasRef: ref al canvas/container (obbligatorio)
 *   - radius: raggio in pixel dal centro - FUORI dal raggio il tracking si DISATTIVA
 *   - autoCenter: se true, calcola automaticamente l'offset dal centro del viewport (default: true)
 *   - transitionSpeed: velocità transizione in/out (0.01-0.2, default: 0.08)
 *
 * Comportamento con radius:
 * - Mouse DENTRO il raggio → tracking attivo, transizione smooth verso posizione mouse
 * - Mouse FUORI dal raggio → tracking disattivato, transizione smooth verso posizione neutra (Mixamo gestisce)
 *
 * Uso base:
 * const { updateTracking } = useMouseTracking(gltf.scene);
 *
 * Uso con tracking locale e auto-center:
 * const containerRef = useRef();
 * const { updateTracking, isInTrackingArea } = useMouseTracking(gltf.scene, {
 *   localTrackingArea: {
 *     canvasRef: containerRef,
 *     radius: 400,           // tracking attivo solo entro 400px dal centro
 *     autoCenter: true,      // calcola offset automaticamente
 *     transitionSpeed: 0.08  // velocità transizione
 *   }
 * });
 *
 * // Nel useFrame:
 * useFrame((state, delta) => {
 *   mixer.update(delta);
 *   updateTracking();
 * });
 */
export function useMouseTracking(scene, options = {}) {
  const {
    head = true,
    neck = true,
    arms = true,
    centerOffset = { x: 0, y: 0 },
    localTrackingArea = null
  } = options;

  const bonesRef = useRef({});
  // Target position (dove vogliamo andare)
  const targetPosition = useRef({ x: 0, y: 0 });
  // Current interpolated position (posizione attuale interpolata)
  const mousePosition = useRef({ x: 0, y: 0 });
  // Tracking state
  const isInTrackingArea = useRef(true);
  // Transition speed
  const transitionSpeed = localTrackingArea?.transitionSpeed ?? 0.08;

  const mouseOffset = useRef({
    headX: 0, headY: 0,
    neckX: 0, neckY: 0,
    eyeX: 0, eyeY: 0,
    armZ: 0
  });

  // Find bones on mount
  useEffect(() => {
    if (scene) {
      bonesRef.current = findBones(scene);
    }
  }, [scene]);

  // Calcola offset automatico dal centro del viewport
  const getAutoCenter = useCallback(() => {
    if (!localTrackingArea?.canvasRef?.current) {
      return { x: 0, y: 0 };
    }

    const canvas = localTrackingArea.canvasRef.current;
    const rect = canvas.getBoundingClientRect();

    // Centro del canvas
    const canvasCenterX = rect.left + rect.width / 2;
    const canvasCenterY = rect.top + rect.height / 2;

    // Centro del viewport
    const viewportCenterX = window.innerWidth / 2;
    const viewportCenterY = window.innerHeight / 2;

    // Offset normalizzato (-1 a 1)
    // Se canvas è a destra del centro viewport, offset è positivo
    const offsetX = (canvasCenterX - viewportCenterX) / viewportCenterX;
    const offsetY = (viewportCenterY - canvasCenterY) / viewportCenterY;

    return { x: offsetX, y: offsetY };
  }, [localTrackingArea]);

  // Calcola posizione mouse e stato tracking
  const calculateMousePosition = useCallback((event) => {
    // Modalità tracking locale (relativo al canvas)
    if (localTrackingArea?.canvasRef?.current) {
      const canvas = localTrackingArea.canvasRef.current;
      const rect = canvas.getBoundingClientRect();

      // Centro del canvas in coordinate viewport
      const canvasCenterX = rect.left + rect.width / 2;
      const canvasCenterY = rect.top + rect.height / 2;

      // Distanza del mouse dal centro del canvas
      const deltaX = event.clientX - canvasCenterX;
      const deltaY = event.clientY - canvasCenterY;

      // Auto-center o offset manuale
      const autoCenter = localTrackingArea.autoCenter !== false; // default true
      const offset = autoCenter ? { x: 0, y: 0 } : centerOffset;

      // Se definito un raggio, controlla se siamo dentro o fuori
      if (localTrackingArea.radius) {
        const radius = localTrackingArea.radius;
        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

        // FUORI dal raggio → disattiva tracking
        if (distance > radius) {
          return {
            x: 0,
            y: 0,
            inRange: false
          };
        }

        // DENTRO il raggio → tracking attivo, normalizza rispetto al raggio
        return {
          x: (deltaX / radius) + offset.x,
          y: -(deltaY / radius) + offset.y,
          inRange: true
        };
      }

      // Senza raggio: usa le dimensioni del canvas come bounds
      const halfWidth = rect.width / 2;
      const halfHeight = rect.height / 2;

      return {
        x: Math.max(-1, Math.min(1, deltaX / halfWidth)) + offset.x,
        y: -Math.max(-1, Math.min(1, deltaY / halfHeight)) + offset.y,
        inRange: true
      };
    }

    // Modalità globale (viewport intero) - comportamento originale + offset
    return {
      x: ((event.clientX / window.innerWidth) * 2 - 1) + centerOffset.x,
      y: (-(event.clientY / window.innerHeight) * 2 + 1) + centerOffset.y,
      inRange: true
    };
  }, [localTrackingArea, centerOffset]);

  // Track mouse position - aggiorna solo il TARGET
  useEffect(() => {
    const handleMouseMove = (event) => {
      const result = calculateMousePosition(event);
      targetPosition.current.x = result.x;
      targetPosition.current.y = result.y;
      isInTrackingArea.current = result.inRange;
    };

    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, [calculateMousePosition]);

  // Interpola la posizione corrente verso il target (chiamato ogni frame)
  const interpolatePosition = useCallback(() => {
    const speed = transitionSpeed;

    // Lerp verso il target
    mousePosition.current.x += (targetPosition.current.x - mousePosition.current.x) * speed;
    mousePosition.current.y += (targetPosition.current.y - mousePosition.current.y) * speed;

    // Snap a zero se molto vicino (evita micro-movimenti infiniti)
    if (Math.abs(mousePosition.current.x) < 0.001) mousePosition.current.x = 0;
    if (Math.abs(mousePosition.current.y) < 0.001) mousePosition.current.y = 0;
  }, [transitionSpeed]);

  // Function to call in useFrame
  const updateTracking = useCallback(() => {
    // Prima interpola la posizione corrente verso il target
    interpolatePosition();

    const {
      head: headBone,
      neck: neckBone,
      leftUpperArm,
      rightUpperArm,
      leftClavicle,
      rightClavicle
    } = bonesRef.current;

    if (head) {
      updateHeadTracking(headBone, mousePosition.current, mouseOffset.current);
    }

    if (neck) {
      updateNeckTracking(neckBone, mousePosition.current, mouseOffset.current);
    }

    if (arms) {
      updateArmTracking(
        { leftUpperArm, rightUpperArm, leftClavicle, rightClavicle },
        mousePosition.current,
        mouseOffset.current,
        true
      );
    }
  }, [interpolatePosition, head, neck, arms]);

  return {
    updateTracking,
    bonesRef,
    mousePosition,
    mouseOffset,
    isInTrackingArea,    // espone se il mouse è nell'area di tracking
    targetPosition,      // espone la posizione target (utile per debug)
  };
}

export default useMouseTracking;
