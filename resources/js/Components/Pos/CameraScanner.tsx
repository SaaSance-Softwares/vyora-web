import React, { useEffect, useState, useRef } from 'react';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

interface CameraScannerProps {
    onScan: (code: string) => void;
    onClose: () => void;
}

export default function CameraScanner({ onScan, onClose }: CameraScannerProps) {
    const [error, setError] = useState<string>('');
    const html5QrCodeRef = useRef<Html5Qrcode | null>(null);

    useEffect(() => {
        const html5QrCode = new Html5Qrcode("reader", {
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
            ]
        });
        html5QrCodeRef.current = html5QrCode;
        let scanned = false;

        const handleSuccess = (decodedText: string) => {
            if (scanned) return;
            scanned = true;
            
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }
            
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                onScan(decodedText);
            }).catch(() => {
                onScan(decodedText);
            });
        };

        const handleError = (err: any) => {
            // Background scan errors (happens every frame it doesn't find a code) - ignore
        };

        const startCamera = async () => {
            try {
                // This forces the permission prompt in a standard way
                const cameras = await Html5Qrcode.getCameras();
                if (cameras && cameras.length > 0) {
                    // Try to intelligently pick the back/rear camera first
                    let selectedCameraId = cameras[0].id;
                    for (const cam of cameras) {
                        const label = (cam.label || '').toLowerCase();
                        if (label.includes('back') || label.includes('rear') || label.includes('environment')) {
                            selectedCameraId = cam.id;
                            break;
                        }
                    }

                    // Call start EXACTLY once to avoid "already under transition" errors
                    await html5QrCode.start(
                        selectedCameraId,
                        { 
                            fps: 15, 
                            disableFlip: false,
                            // Setting qrbox focuses the scanner on the center, dramatically improving mobile 2D/QR detection speed
                            qrbox: (videoWidth, videoHeight) => {
                                const minEdge = Math.min(videoWidth, videoHeight);
                                const size = Math.floor(minEdge * 0.75); // 75% of the screen
                                return { width: size, height: size };
                            }
                        },
                        handleSuccess,
                        handleError
                    );
                } else {
                    setError("No cameras found on this device.");
                }
            } catch (err: any) {
                console.error("Camera permission error:", err);
                setError("Camera error: " + (err?.name || err?.message || String(err)));
            }
        };

        startCamera();

        return () => {
            if (html5QrCodeRef.current && html5QrCodeRef.current.isScanning) {
                html5QrCodeRef.current.stop().then(() => {
                    html5QrCodeRef.current?.clear();
                }).catch(console.error);
            }
        };
    }, []);

    return (
        <div className="fixed inset-0 z-[300] bg-black flex flex-col" onClick={e => e.stopPropagation()}>
            {/* Header */}
            <div className="flex items-center justify-between px-4 py-3 bg-black/80 backdrop-blur-sm z-20">
                <div className="flex items-center gap-2">
                    <div className="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span className="text-white font-bold text-sm uppercase tracking-wider">Scanning...</span>
                </div>
                <button
                    onClick={onClose}
                    className="text-white bg-white/10 hover:bg-white/20 w-9 h-9 rounded-full flex items-center justify-center text-lg transition"
                >
                    ✕
                </button>
            </div>

            {/* Viewfinder Area */}
            <div className="flex-1 relative overflow-hidden flex flex-col items-center justify-center bg-black">
                {error ? (
                    <div className="text-center px-8 z-20">
                        <div className="text-4xl mb-4">📵</div>
                        <p className="text-white font-semibold mb-2">{error}</p>
                        <button onClick={onClose} className="mt-4 bg-white text-black px-6 py-2 rounded-full font-bold text-sm">
                            Go Back
                        </button>
                    </div>
                ) : (
                    <>
                        <div className="w-full h-full absolute inset-0">
                            {/* The div where html5-qrcode injects the video stream */}
                            <div id="reader" className="w-full h-full [&>video]:object-cover [&>video]:w-full [&>video]:h-full"></div>
                        </div>

                        {/* Dark overlay with cutout - restored from original design */}
                        <div className="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
                            <div className="absolute top-0 left-0 right-0 h-[25%] bg-black/60" />
                            <div className="absolute bottom-0 left-0 right-0 h-[25%] bg-black/60" />
                            <div className="absolute top-[25%] bottom-[25%] left-0 w-[15%] bg-black/60" />
                            <div className="absolute top-[25%] bottom-[25%] right-0 w-[15%] bg-black/60" />

                            <div className="relative w-[70%] h-[50%]">
                                <div className="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white rounded-tl-lg" />
                                <div className="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white rounded-tr-lg" />
                                <div className="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white rounded-bl-lg" />
                                <div className="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white rounded-br-lg" />

                                <div
                                    className="absolute left-2 right-2 h-0.5 bg-gradient-to-r from-transparent via-green-400 to-transparent opacity-90"
                                    style={{ animation: 'scanline 2s ease-in-out infinite' }}
                                />
                            </div>
                        </div>
                    </>
                )}

                {/* Hint text */}
                <div className="absolute bottom-8 left-0 right-0 flex justify-center pointer-events-none z-20">
                    <span className="bg-black/60 text-white text-xs font-medium px-4 py-2 rounded-full backdrop-blur-sm shadow-lg">
                        Point camera at a barcode or QR code
                    </span>
                </div>
            </div>
            
            <style>{`
                @keyframes scanline {
                    0%   { top: 4px; opacity: 1; }
                    50%  { top: calc(100% - 4px); opacity: 1; }
                    100% { top: 4px; opacity: 1; }
                }
                /* Hide the default html5-qrcode overlay elements since we made our own */
                #qr-shaded-region { display: none !important; }
            `}</style>
        </div>
    );
}
