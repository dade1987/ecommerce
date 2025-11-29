import { useEffect, useRef } from 'react';
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
 * Uso:
 * const { updateTracking } = useMouseTracking(gltf.scene, { head: true, neck: true, arms: true });
 *
 * // Nel useFrame:
 * useFrame((state, delta) => {
 *   mixer.update(delta);
 *   updateTracking(); // Chiama questo per aggiornare il tracking
 * });
 */
export function useMouseTracking(scene, options = {}) {
  const { head = true, neck = true, arms = true } = options;

  const bonesRef = useRef({});
  const mousePosition = useRef({ x: 0, y: 0 });
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

  // Track mouse position
  useEffect(() => {
    const handleMouseMove = (event) => {
      mousePosition.current.x = (event.clientX / window.innerWidth) * 2 - 1;
      mousePosition.current.y = -(event.clientY / window.innerHeight) * 2 + 1;
    };

    window.addEventListener('mousemove', handleMouseMove);
    return () => window.removeEventListener('mousemove', handleMouseMove);
  }, []);

  // Function to call in useFrame
  const updateTracking = () => {
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
  };

  return {
    updateTracking,
    bonesRef,
    mousePosition,
    mouseOffset,
  };
}

export default useMouseTracking;