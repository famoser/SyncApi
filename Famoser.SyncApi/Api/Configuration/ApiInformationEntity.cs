using System;

namespace Famoser.SyncApi.Api.Configuration
{
    public class ApiInformationEntity
    {
        public int Seed { get; set; }
        public int Modulo { get; set; }
        public bool DeviceNeedAuthentication { get; set; }
        public Uri Uri { get; set; }
    }
}
