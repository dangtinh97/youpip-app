@extends('app.call-layout')
@section('content')
    <div class="main">
        <div class="d-none">
            <audio id="remoteVideo" autoplay></audio>
            <audio id="localVideo" autoplay></audio>
        </div>
        <div class="avatar">
            <div class="text-light mb-3 fs-3">Friend</div>
            <img alt="avt" class="mt-4" width="160"
                 src="https://static-00.iconduck.com/assets.00/avatar-default-icon-248x256-bdv8pdld.png">
            <p class="text-light" id="call-time">ringing...</p>
        </div>
        <div class="call-action">
            <img alt="end-call" id="end-call" src="{{asset('img/end-call.png')}}" width="64px">
        </div>
    </div>
    @push('script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.1/socket.io.js"
                integrity="sha512-xbQU0+iHqhVt7VIXi6vBJKPh3IQBF5B84sSHdjKiSccyX/1ZI7Vnkt2/8y8uruj63/DVmCxfUNohPNruthTEQA=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            let localStream;
            let sender;
            const remoteVideo = document.querySelector('#remoteVideo');
            const JOIN_CALL = "JOIN_CALL";
            const MESSAGE = "MESSAGE";
            const LEAVE_ROOM = "LEAVE_ROOM";
            const SEND_DATA = "SEND_DATA";
            let stream = null;
            const constraints = {
                'video': false,
                'audio': true
            }
            const configuration = {
                'iceServers': [
                    {
                        'urls': 'stun:stun.l.google.com:19302'
                    },
                    {
                        'urls': 'stun:stun1.l.google.com:19302'
                    },
                    {
                        "urls":"turn:192.158.29.39:3478?transport=udp",
                        "username":"28224511:1379330808",
                        "credential":"JZEOEt2V3Qb0y27GRntt2u2PAYA="
                    }
                ]
            }
            let peerConnection = new RTCPeerConnection(configuration)
            document.addEventListener("DOMContentLoaded", function () {
                let dataJson = @json($answer);
                const socket = io("https://youpip.net:3001/", {
                    reconnectionDelayMax: 10000,
                    auth: {
                        token: dataJson.token
                    },
                });


                socket.on('connect',()=>{
                    console.log('socket connect!')

                    socket.on(SEND_DATA,(data)=>{
                        console.log('data socket',data)
                        if(data.type==="OFFER"){
                            counterTime('#call-time')
                            listenOffer(data.offer);
                        }
                        if(data.type==="OFFER"){
                            listenICE(data.iceCandidate)
                        }
                        if(data.type==="DISCONNECT"){
                            onClose();
                        }
                    })
                })

                let listenOffer = async (offer)=>{
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(offer))
                    const answer = await peerConnection.createAnswer()
                    await peerConnection.setLocalDescription(answer)
                    emitData("ANSWER",{
                        'answer': answer
                    })
                }

                let requestPermission = () => {
                    navigator.mediaDevices.getUserMedia(constraints)
                        .then(async streamPermission => {
                            console.log(streamPermission)
                            stream = streamPermission
                            const videoCameras = await getConnectedDevices('videoinput')
                            const streamCamera = await openCamera(videoCameras[0].deviceId, 1280, 720)
                            localStream = streamCamera;
                            localStream.getTracks().forEach(track => {
                                sender = peerConnection.addTrack(track, localStream);
                            });
                            joinRoom();
                            sendNotification();
                        })
                        .catch(error => {
                            console.log(error)
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Yêu cầu sử dụng micro cho cuộc gọi này.',
                                allowOutsideClick: false,
                                showConfirmButton: false
                            })
                        });
                }
                requestPermission();
                let joinRoom = () => {
                    socket.emit(JOIN_CALL, {
                        room_oid: dataJson.room_id,
                    })

                    setTimeout(()=>{
                        emitData('IS_ONLINE',{
                            room_oid: dataJson.room_id
                        })
                    })
                }

                let sendNotification = ()=>{
                    $.ajax({
                        url:'{{route('call.send-notification')}}',
                        type:"GET",
                        dataType:"JSON",
                        data:{
                            fbid: dataJson.connect_with,
                            room_id: dataJson.room_id
                        },
                        success:(data)=>{
                            console.log(data)
                        }
                    })
                }
                async function makeCall () {
                    const offer = await peerConnection.createOffer()
                    await peerConnection.setLocalDescription(offer)
                    socket.emit(SEND_DATA,{
                        type:'offer',
                        room_oid: dataJson.room_id,
                        offer: offer
                    })
                }

                let emitData = (type, data)=>{
                    socket.emit(SEND_DATA,{
                        ...data,
                        type:type,
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
                    emitData("DISCONNECT");
                    $.ajax({
                        url:'{{route('call.destroy',$answer['room_id'])}}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type:"DELETE",
                        dataType:"JSON",
                        success:(res)=>{
                            console.log(res)
                        }
                    })
                    onClose();
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
