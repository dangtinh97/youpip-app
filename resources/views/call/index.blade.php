@extends('app.call-layout')
@section('content')
    <div class="main">
        <div class="d-none">
            <video id="remoteVideo" autoplay></video>
            <video id="localVideo" autoplay></video>
        </div>
        <div class="avatar">
            <div class="text-light mb-3 fs-3">Friend</div>
            <img alt="avt" class="mt-4" width="160"
                 src="https://static-00.iconduck.com/assets.00/avatar-default-icon-248x256-bdv8pdld.png">
            <p class="text-light" id="time-call">calling...</p>
        </div>
        <div class="call-action">
            <img id="end-call" alt="end-call" src="{{asset('img/end-call.png')}}" width="64px">
        </div>
    </div>
    @push('script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.1/socket.io.js"
                integrity="sha512-xbQU0+iHqhVt7VIXi6vBJKPh3IQBF5B84sSHdjKiSccyX/1ZI7Vnkt2/8y8uruj63/DVmCxfUNohPNruthTEQA=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            const remoteVideo = document.querySelector('#remoteVideo');
            const localVideo = document.querySelector('#localVideo');
            let localStream;
            const JOIN_CALL = "JOIN_CALL";
            const MESSAGE = "MESSAGE";
            const LEAVE_ROOM = "LEAVE_ROOM";
            const SEND_DATA = "SEND_DATA";
            let stream = null;
            let audioFirst;
            const constraints = {
                'video': false,
                'audio': true
            }
            const configuration = {
                'iceServers': @json($ice)
            }
            let peerConnection = new RTCPeerConnection(configuration)
            document.addEventListener("DOMContentLoaded", function () {
                let dataJson = @json($call);
                const socket = io("https://youpip.net:3001/", {
                    reconnectionDelayMax: 10000,
                    auth: {
                        token: dataJson.token
                    },
                });
                Swal.fire({
                    icon: 'question',
                    title: 'Cuộc gọi thoại',
                    text: 'Bạn muốn thực hiện cuộc gọi này?',
                    allowOutsideClick: false,
                    showConfirmButton: true
                }).then(() => {
                    requestPermission();
                })
                socket.on('connect', () => {
                    console.log('socket connect!')

                    socket.on(SEND_DATA, (data) => {
                        console.log('data socket', data)

                        if (data.type === 'IS_ONLINE') {
                            isOnline();
                        }
                        if (data.type === 'ANSWER') {
                            listenAnswer(data.answer);
                        }

                        if(data.type==="OFFER"){
                            listenICE(data.iceCandidate)
                        }

                        if(data.type==="DISCONNECT"){
                            onClose();
                        }

                    })
                })
                let listenAnswer =async (answer) => {
                    const remoteDesc = new RTCSessionDescription(answer)
                    await peerConnection.setRemoteDescription(remoteDesc)
                }

                let isOnline = () => {
                    // audioFirst.pause()
                    makeCall();
                    counterTime('#time-call')
                }
                let sender ;
                let requestPermission = () => {
                    navigator.mediaDevices.getUserMedia(constraints)
                        .then(async streamPermission => {
                            stream = streamPermission;
                            localVideo.src = streamPermission;
                            const videoCameras = await getConnectedDevices('videoinput')
                            const streamCamera = await openCamera(videoCameras[0].deviceId, 1280, 720)
                            localStream = streamCamera;
                            localStream.getTracks().forEach(track => {
                                sender = peerConnection.addTrack(track, localStream)
                            });

                            {{--audioFirst = new Audio('{{asset('audio/tut-tut.mp3')}}');--}}
                            {{--await audioFirst.play();--}}
                            // joinRoom();
                            // sendNotification();
                            // setTimeout(() => {
                            //     makeCall()
                            // }, 1000)
                        })
                        .catch(error => {
                            console.log(error)
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Yêu cầu sử dụng micro cho cuộc gọi này.'+error,
                                allowOutsideClick: false,
                                showConfirmButton: false
                            })
                        });
                }

                let joinRoom = () => {
                    socket.emit(JOIN_CALL, {
                        room_oid: dataJson.room_id,
                    })
                }
                let sendNotification = () => {
                    $.ajax({
                        url: '{{route('call.send-notification')}}',
                        type: "GET",
                        dataType: "JSON",
                        data: {
                            fbid: dataJson.connect_with,
                            room_id: dataJson.room_id
                        },
                        success: (data) => {
                            console.log(data)
                        }
                    })
                }
                async function makeCall() {
                    const offer = await peerConnection.createOffer()
                    await peerConnection.setLocalDescription(offer)
                    emitData("OFFER", {
                        offer: offer
                    })
                }

                let emitData = (type, data) => {
                    socket.emit(SEND_DATA, {
                        ...data,
                        type: type,
                        room_oid: dataJson.room_id
                    })
                }

                peerConnection.addEventListener('track', async (event) => {
                    const [remoteStream] = event.streams;
                    remoteVideo.srcObject = remoteStream;
                });

                peerConnection.addEventListener('connectionstatechange', event => {
                    if (peerConnection.connectionState === 'connected') {
                        // Peers connected!
                    }
                })

                peerConnection.addEventListener('icecandidate', event => {
                    if (event.candidate) {
                        emitData('iceCandidate',{'iceCandidate': event.candidate});
                    }
                })

                const listenICE =async (iceCandidate)=>{
                    try {
                        await peerConnection.addIceCandidate(iceCandidate);
                    } catch (e) {
                        console.error('Error adding received ice candidate', e);
                    }
                }


                async function getConnectedDevices (type) {
                    const devices = await navigator.mediaDevices.enumerateDevices()
                    return devices.filter(device => device.kind === type)
                }
                // Open camera with at least minWidth and minHeight capabilities
                async function openCamera (cameraId, minWidth, minHeight) {
                    const constraints = {
                        'audio': { 'echoCancellation': true },
                        'video': {
                            'deviceId': cameraId,
                        }
                    }

                    return await navigator.mediaDevices.getUserMedia(constraints)
                }

                $(this).on('click','#end-call',()=>{
                    emitData("DISCONNECT")
                    onClose()
                    $.ajax({
                        url:'{{route('call.destroy',$call['room_id'])}}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type:"DELETE",
                        dataType:"JSON",
                        success:(res)=>{
                            console.log(res)
                        }
                    })
                })

                let onClose = ()=>{
                    peerConnection.removeTrack(sender)
                    setTimeout(()=>{
                        window.location.href = 'https://docs.google.com/forms/d/e/1FAIpQLSe2yEaAG9luLpgpm259acXuc69cHpFX_M243cNYDe4xJMt_NA/viewform'
                    },500)
                }
            })
        </script>
    @endpush
@endsection
