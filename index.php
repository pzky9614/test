<?php

$videoDir = 'mp4/'; // 假设视频文件存储在项目根目录下的 mp4/ 文件夹中

// 检查目录是否存在并且可读
if (is_dir($videoDir) && is_readable($videoDir)) {
    // 使用 scandir 获取目录中的所有文件，并过滤掉 '.' 和 '..'
    $videoFiles = array_diff(scandir($videoDir), array('.', '..'));
} else {
    // 如果目录不存在或者不可读，设置 $videoFiles 为一个空数组
    $videoFiles = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Player</title>
    <style>
        video {
            width: 70%;
            max-width: 70%;
            height: auto;
        }
        body {
            margin: 0;
            padding: 10px;
            background-color: black;
            color: white;
        }
        .video-list {
            margin-bottom: 20px;
        }
        .video-list a {
            display: inline-block;
            margin: 5px 0;
            text-decoration: none;
            color: #0066cc;
            margin-right: 10px;
        }
        .delete-btn {
            display: inline-block;
            padding: 5px 10px;
            color: white;
            background-color: red;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

    <video id="myVideo" controls>
        Your browser does not support HTML video.
    </video>

    <a href="#" onclick="play();" class="responsive-btn">Click Me</a>
    
    <div class="video-list">
        <h2>Available Videos</h2>
        <?php
        // 检查 $videoFiles 是否不为空
        if (!empty($videoFiles)) {
            foreach ($videoFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'mp4') {
                    echo '<div class="video-item">';
                    echo '<a href="#" class="video-link" data-file="' . $videoDir . $file . '">' . $file . '</a>';
                    echo '<button class="delete-btn" data-file="' . $videoDir . $file . '">Delete</button>';
                    echo '</div>';
                }
            }
        } else {
            echo '<p>No videos found in the directory.</p>';
        }
        ?>
    </div>

    <script>
        function play() {
            fetch(`video_progress.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.video_file && data.progress_time) {
                        // 自动设置视频源和进度
                        video.src = data.video_file;
                        video.currentTime = data.progress_time;
                        currentFile = data.video_file;
                        video.play();
                    }
                });
        }

        const video = document.getElementById('myVideo');
        let saveInterval;
        let currentFile = ''; // 当前播放的视频文件
        const userId = 'unique_user_id'; // 用户唯一ID

        // 获取进度
        function getProgress(videoFile) {
            fetch(`video_progress.php?user_id=${userId}&video_file=${videoFile}`)
                .then(response => response.json())
                .then(data => {
                    if (data.progress_time) {
                        video.currentTime = data.progress_time;
                    }
                });
        }

        // 保存进度
        function saveProgress() {
            if (currentFile) {
                const progressTime = video.currentTime;
                fetch('video_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&video_file=${currentFile}&progress_time=${progressTime}`
                });
            }
        }

        // 点击视频链接，开始播放选定的视频
        document.querySelectorAll('.video-link').forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const file = link.getAttribute('data-file');
                video.src = file;
                currentFile = file;
                video.play();
                
                // 获取上次保存的进度
                getProgress(file);
            });
        });

        // 删除视频文件
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const file = button.getAttribute('data-file');

                if (confirm(`Are you sure you want to delete ${file}?`)) {
                    // 发起删除请求
                    fetch('delete_video.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `video_file=${encodeURIComponent(file)}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            alert('Video deleted successfully');
                            // 从页面中移除视频元素
                            button.parentElement.remove();
                        } else {
                            alert('Failed to delete the video.');
                        }
                    });
                }
            });
        });

        // 视频播放时，每 10 秒保存一次
        video.addEventListener('play', () => {
            saveInterval = setInterval(saveProgress, 10000); // 每 10 秒保存一次
        });

        // 暂停时，保存当前进度并清除定时器
        video.addEventListener('pause', () => {
            clearInterval(saveInterval);
            saveProgress(); // 立即保存
        });

        // 视频结束时，清除保存的数据
        video.addEventListener('ended', () => {
            clearInterval(saveInterval);
            saveProgress();
        });

        // 页面卸载时保存进度
        window.addEventListener('beforeunload', saveProgress);

        // 监听空格键暂停或播放视频
        document.addEventListener('keydown', function(event) {
            if (event.code === 'Space') {
                event.preventDefault(); // 阻止页面滚动
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
            }
        });
    </script>

</body>
</html>
