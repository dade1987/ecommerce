import React, { Suspense } from 'react';
import { Canvas } from '@react-three/fiber';
import { Environment, OrthographicCamera, OrbitControls, Loader } from '@react-three/drei';

import Avatar from '../Avatar';
import Background from './Background';
import { ASSETS_BASE, CAMERA_PRESETS } from '../constants';

/**
 * 3D Scene component containing Canvas, Camera, Controls, and Avatar
 */
export function Scene3D({
  modelUrl,
  avatarView,
  orbitControls,
  transparentBackground,
  backgroundImage,
  speak,
  setSpeak,
  text,
  setAudioSource,
  playing,
  ttsEndpoint,
  enableBoneControls,
  showLevaPanel,
}) {
  // Get camera settings based on view
  const cameraSettings = CAMERA_PRESETS[avatarView] || CAMERA_PRESETS.bust;

  return (
    <>
      <Canvas
        dpr={2}
        gl={{ alpha: transparentBackground, antialias: true }}
        onCreated={(ctx) => {
          ctx.gl.physicallyCorrectLights = true;
          if (transparentBackground) {
            ctx.gl.setClearColor(0x000000, 0);
          }
        }}
      >
        <OrthographicCamera
          makeDefault
          zoom={cameraSettings.zoom}
          position={[0, cameraSettings.posY, 1]}
        />

        {/* OrbitControls: 'none' = disabled, 'limited' = constrained, 'debug' = free */}
        {orbitControls !== 'none' && (
          <OrbitControls
            target={[0, cameraSettings.targetY, 0]}
            enableZoom={true}
            enablePan={orbitControls === 'debug'}
            enableRotate={true}
            minZoom={orbitControls === 'debug' ? 100 : cameraSettings.zoom * 0.8}
            maxZoom={orbitControls === 'debug' ? 3000 : cameraSettings.zoom * 1.2}
            minPolarAngle={orbitControls === 'debug' ? 0 : Math.PI / 2.5}
            maxPolarAngle={orbitControls === 'debug' ? Math.PI : Math.PI / 1.8}
            minAzimuthAngle={orbitControls === 'debug' ? -Infinity : -Math.PI / 6}
            maxAzimuthAngle={orbitControls === 'debug' ? Infinity : Math.PI / 6}
          />
        )}

        <Suspense fallback={null}>
          <Environment
            background={false}
            files={`${ASSETS_BASE}/images/photo_studio_loft_hall_1k.hdr`}
          />
        </Suspense>

        {backgroundImage && (
          <Suspense fallback={null}>
            <Background backgroundImage={backgroundImage} />
          </Suspense>
        )}

        <Suspense fallback={null}>
          <Avatar
            avatarUrl={modelUrl}
            speak={speak}
            setSpeak={setSpeak}
            text={text}
            setAudioSource={setAudioSource}
            playing={playing}
            ttsEndpoint={ttsEndpoint}
            enableBoneControls={enableBoneControls}
            showLevaPanel={showLevaPanel}
          />
        </Suspense>
      </Canvas>

      <Loader dataInterpolation={(p) => `Caricamento... attendere`} />
    </>
  );
}

export default Scene3D;
