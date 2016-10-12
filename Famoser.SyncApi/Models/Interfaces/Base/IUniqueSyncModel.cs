using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Models.Interfaces.Base
{
    public interface IUniqueSyncModel : IBaseSyncModel
    {
        /// <summary>
        /// Get unique identifier (different for all objects) for the API
        /// </summary>
        /// <returns></returns>
        string GetUniqeIdentifier();
    }
}
