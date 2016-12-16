using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiTraceService : IExceptionLogger
    {
        void TraceSuccessfulRequest(object request, string link);
        void TraceFailedRequest(object request, string link, string message);
    }
}
