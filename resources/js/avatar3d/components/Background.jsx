import React from 'react';
import { useTexture } from '@react-three/drei';

/**
 * 3D Background plane component
 */
export function Background({ backgroundImage }) {
  const texture = useTexture(backgroundImage);

  if (!backgroundImage) return null;

  return (
    <mesh position={[0, 1.5, -4]} scale={[1.2, 1.2, 1.2]}>
      <planeGeometry />
      <meshBasicMaterial map={texture} />
    </mesh>
  );
}

export default Background;
