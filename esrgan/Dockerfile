FROM ubuntu
ENTRYPOINT ["python3"]
CMD ["test.py"]
RUN apt-get update && apt-get install -y python3 pip gfortran git wget
RUN pip install numpy opencv-python torch
RUN mkdir -p /src/ && cd /src && git clone https://github.com/xinntao/ESRGAN
WORKDIR /src/ESRGAN
RUN wget "https://drive.google.com/uc?export=download&id=1TPrz5QKd8DHHt1k8SRtm6tMiPjz_Qene" -O /src/ESRGAN/models/RRDB_ESRGAN_x4.pth
RUN wget "https://drive.google.com/uc?export=download&id=1pJ_T-V1dpb1ewoEra1TGSWl5e6H7M4NN" -O /src/ESRGAN/models/RRDB_PSNR_x4.pth
ADD ./convert.py /src/ESRGAN/convert.py
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get install -y libgl1-mesa-dev libglib2.0-dev
