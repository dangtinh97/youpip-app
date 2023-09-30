<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Call</title>
</head>
<body>
<div id="alert"></div>
<video id="localVideo" autoplay playsinline controls="false"></video>
<video id="remoteVideo" autoplay playsinline controls="false"></video>
<button type="button" id="make-call">Make call</button>
<button type="button" id="answer">answer</button>
<div id="wss"></div>

<script
    src="https://code.jquery.com/jquery-3.7.1.js"
    integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>
<script>
  const URL_WS = 'wss://socketsbay.com/wss/v2/1/demo/'
  document.addEventListener('DOMContentLoaded', () => {
    const ws = new WebSocket(URL_WS)

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
    console.log(peerConnection)
    ws.onopen = () => {
      console.log('open')
    }
    ws.onmessage = async function (e) {
      const data = e.data
      $('#wss').append(`<p>${data}</p>`)
      console.log(data)
      try {
        const message = JSON.parse(data)
        if (message.iceCandidate) {
          try {
            await peerConnection.addIceCandidate(message.iceCandidate);
          } catch (e) {
            console.error('Error adding received ice candidate', e);
          }
        }

        console.log('message', message)
        if (message.offer) {
          showAlert('offer')
          await peerConnection.setRemoteDescription(new RTCSessionDescription(message.offer))
          const answer = await peerConnection.createAnswer()
          await peerConnection.setLocalDescription(answer)
          ws.send(JSON.stringify({ 'answer': answer }))
        }

        if (message.answer) {
          const remoteDesc = new RTCSessionDescription(message.answer)
          await peerConnection.setRemoteDescription(remoteDesc)
          showAlert('answer')
        }

      } catch (e) {
        console.error(e)
      }
    }
    const openMediaDevices = async (constraints) => {
      return await navigator.mediaDevices.getUserMedia(constraints)
    }
    let localStream;

    const stream = openMediaDevices({ 'video': true, 'audio': true })
    stream.then(async () => {
      const videoCameras = await getConnectedDevices('videoinput')
      const stream = await openCamera(videoCameras[0].deviceId, 1280, 720)
      const videoElement = document.querySelector('video#localVideo')
      videoElement.muted = true
      videoElement.srcObject = stream
      localStream = stream;
      localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

    })

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
          // 'width': {'min': minWidth},
          // 'height': {'min': minHeight}
        }
      }

      return await navigator.mediaDevices.getUserMedia(constraints)
    }

    async function makeCall () {
      const offer = await peerConnection.createOffer()
      await peerConnection.setLocalDescription(offer)
      ws.send(JSON.stringify({
        'offer': offer
      }))
    }

    $('#make-call').on('click', () => {
      makeCall().then()
    })


    peerConnection.addEventListener('icecandidate', event => {
      if (event.candidate) {
        showAlert(event.candidate)
        ws.send(JSON.stringify({'iceCandidate': event.candidate}));
      }
    })

    peerConnection.addEventListener('connectionstatechange', event => {
      if (peerConnection.connectionState === 'connected') {
        // Peers connected!
      }
    })

    function showAlert(msg){
      $('#alert').text(msg)
    }

    const remoteVideo = document.querySelector('#remoteVideo');

    peerConnection.addEventListener('track', async (event) => {
      showAlert("track")
      const [remoteStream] = event.streams;
      remoteVideo.muted  = true;
      remoteVideo.srcObject = remoteStream;

    });
  })
</script>
</body>
</html>
